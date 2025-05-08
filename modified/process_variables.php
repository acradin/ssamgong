<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib.inc.php";

$api_url = 'http://43.200.255.42:8000';

// 포인트 관련 상수 정의
define('POINT_PER_USAGE', 10);        // 사용당 차감 포인트
define('FREE_USAGE_LIMIT', 10);         // 무료 사용 가능 횟수

// 세션 체크
if (!$_SESSION['_mt_idx']) {
    echo json_encode([
        'success' => false,
        'message' => '로그인이 필요합니다.'
    ]);
    exit;
}

try {
    // 1. 필수 파라미터 체크
    if (!isset($_POST['ct_idx'])) {
        throw new Exception('잘못된 접근입니다.');
    }

    $categoryId = (int)$_POST['ct_idx'];

    // 2. 필수 변수 체크
    $required_vars = $DB->rawQuery("
        SELECT cv_idx, cv_name, cv_type, cv_required 
        FROM chatbot_variable_t 
        WHERE ct_idx = ? AND cv_status = 'Y'",
        [$categoryId]
    );

    // 필수 변수 검증
    foreach ($required_vars as $var) {
        $var_key = 'var_' . $var['cv_idx'];
        
        if ($var['cv_required'] === 'Y') {
            if ($var['cv_type'] === 'file') {
                if (!isset($_FILES[$var_key]) || $_FILES[$var_key]['error'] === UPLOAD_ERR_NO_FILE) {
                    throw new Exception("{$var['cv_name']} 파일을 첨부해주세요.");
                }
            } else {
                if (!isset($_POST[$var_key]) || trim($_POST[$var_key]) === '') {
                    throw new Exception("{$var['cv_name']}을(를) 입력해주세요.");
                }
                
                // 문제 수 검증 추가
                if (strpos($var['cv_name'], '문제 수') !== false) {
                    $problem_count = (int)$_POST[$var_key];
                    if ($problem_count > 20) {
                        throw new Exception("문제 수는 20개를 초과할 수 없습니다.");
                    }
                }
            }
        }
    }

    // 3. 포인트 체크 및 차감
    // 이번 달 사용 횟수 확인 (chat_messages 테이블 기준)
    $current_month_start = date('Y-m-01 00:00:00');
    $current_month_end = date('Y-m-t 23:59:59');
    
    $monthly_usage = $DB->rawQueryOne("
        SELECT COUNT(*) as usage_count
        FROM chat_messages cm
        JOIN chat_sessions cs ON cm.cs_idx = cs.cs_idx
        WHERE cs.mt_idx = ?
        AND cm.is_bot = 0
        AND cm.created_at BETWEEN ? AND ?",
        [$_SESSION['_mt_idx'], $current_month_start, $current_month_end]
    );

    // FREE_USAGE_LIMIT 이상 사용한 경우에만 포인트 체크
    if ($monthly_usage['usage_count'] >= FREE_USAGE_LIMIT) {
        $point_check = $DB->rawQueryOne("
            SELECT mt_point 
            FROM member_t 
            WHERE mt_idx = ? 
            AND mt_point >= ?",
            [$_SESSION['_mt_idx'], POINT_PER_USAGE]
        );

        if (!$point_check) {
            throw new Exception('포인트가 부족합니다.');
        }
    }

    // 4. API 호출을 위한 데이터 준비
    $category = $DB->rawQueryOne("
        SELECT ct.ct_name, parent.ct_name as parent_name, parent.ct_idx as parent_idx
        FROM category_t ct
        JOIN category_t parent ON ct.parent_idx = parent.ct_idx
        WHERE ct.ct_idx = ?",
        [$categoryId]
    );

    // API 엔드포인트 결정
    $is_problem_creation = ($category['parent_name'] === '문제 제작');

    if ($is_problem_creation) {
        // 변수명 매핑
        $param_map = [
            '학교급' => 'school_level',
            '학년' => 'grade',
            '출제과목' => 'subject',
            '출제 종류' => 'exam_type',
            '문제 수' => 'num_problems',
            '난이도' => 'difficulty',
            '문제종류' => 'problem_type',
            '참고 자료' => 'file',
            '기타 요구사항' => 'additional_prompt'
        ];

        $api_data = [];
        foreach ($required_vars as $var) {
            $var_key = 'var_' . $var['cv_idx'];
            $api_key = $param_map[$var['cv_name']] ?? null;
            if (!$api_key) continue;

            if ($var['cv_type'] === 'file') {
                if (isset($_FILES[$var_key]) && $_FILES[$var_key]['error'] === UPLOAD_ERR_OK) {
                    $api_data[$api_key] = new CURLFile(
                        $_FILES[$var_key]['tmp_name'],
                        $_FILES[$var_key]['type'],
                        $_FILES[$var_key]['name']
                    );
                }
            } else {
                if (isset($_POST[$var_key])) {
                    $api_data[$api_key] = $_POST[$var_key];
                }
            }
        }

        // FastAPI 서버로 요청 전송 (generate_problems)
        $ch = curl_init($api_url . '/generate_problems/');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $api_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            throw new Exception('API 호출 실패');
        }

        $result = json_decode($response, true);

        // 리턴값: { "session_id": ..., "content": ..., "conversation": ... }
        $ai_conversation = $result['conversation'] ?? '';
        $ai_content = $result['content'] ?? '';
        $session_id = $result['session_id'] ?? uniqid('problem_', true);
    } else {
        // Claude API 호출 (run_claude)
        $prompt = $DB->rawQueryOne("
            SELECT cp_content
            FROM chatbot_prompt_t
            WHERE parent_ct_idx = ? AND ct_idx = ? AND cp_status = 'Y'
            ORDER BY cp_idx DESC
            LIMIT 1",
            [$category['parent_idx'], $categoryId]
        );

        $system_prompt = $prompt ? $prompt['cp_content'] : "당신은 {$category['parent_name']} 전문가입니다.";
        
        $user_prompt = "";
        foreach ($required_vars as $var) {
            $var_key = 'var_' . $var['cv_idx'];
            if (isset($_POST[$var_key])) {
                $user_prompt .= "{$var['cv_name']}: {$_POST[$var_key]}\n";
            }
        }
        
        $api_data = [
            'system_prompt' => $system_prompt,
            'user_prompt' => $user_prompt
        ];

        $ch = curl_init($api_url . '/run_claude/');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            throw new Exception('API 호출 실패');
        }

        $result = json_decode($response, true);

        // 리턴값: { "session_id": ..., "content": ..., "conversation": ... }
        $ai_conversation = $result['conversation'] ?? '';
        $ai_content = $result['content'] ?? '';
        $session_id = $result['session_id'] ?? uniqid('chat_', true);
    }
    /*
    // 5. 테스트용 응답 생성
    $result = [
        'success' => true,
        'session_id' => uniqid('test_', true)
    ];
    */

    // 6. DB 트랜잭션 시작
    $DB->startTransaction();

    try {
        // 세션 생성
        $DB->rawQuery("
            INSERT INTO chat_sessions 
            (session_id, mt_idx, ct_idx, created_at, status, last_ai_result, last_ai_result_type) 
            VALUES (?, ?, ?, NOW(), 'active', ?, ?)",
            [$session_id, $_SESSION['_mt_idx'], $categoryId, $ai_conversation, $is_problem_creation ? 'problem' : 'chat']
        );
        $cs_idx = $DB->getInsertId();

        // 변수 값 저장
        foreach ($required_vars as $var) {
            $var_key = 'var_' . $var['cv_idx'];
            $value = '';

            if ($var['cv_type'] === 'file' && isset($_FILES[$var_key])) {
                $value = $_FILES[$var_key]['name'];
            } else if (isset($_POST[$var_key])) {
                $value = $_POST[$var_key];
            }

            if ($value !== '') {
                $DB->rawQuery("
                    INSERT INTO chat_variable_values 
                    (cs_idx, cv_idx, value) 
                    VALUES (?, ?, ?)",
                    [$cs_idx, $var['cv_idx'], $value]
                );
            }
        }

        // 1. 사용자 메시지(질문) 저장
        $user_message = '';
        foreach ($required_vars as $var) {
            $var_key = 'var_' . $var['cv_idx'];
            if (isset($_POST[$var_key])) {
                $user_message .= "{$var['cv_name']}: {$_POST[$var_key]}\n";
            }
        }
        if (trim($user_message) !== '') {
            $DB->rawQuery("
                INSERT INTO chat_messages 
                (cs_idx, content, is_bot, created_at) 
                VALUES (?, ?, 0, NOW())",
                [$cs_idx, trim($user_message)]
            );
        }

        // 2. AI 응답(문제 생성 결과) 저장
        if (trim($ai_content) !== '') {
            $DB->rawQuery("
                INSERT INTO chat_messages 
                (cs_idx, content, is_bot, created_at) 
                VALUES (?, ?, 1, NOW())",
                [$cs_idx, trim($ai_content)]
            );
        }

        // 첫 사용 기록 체크 및 저장
        $usage_check = $DB->rawQueryOne("
            SELECT id 
            FROM chatbot_usage 
            WHERE mt_idx = ?",
            [$_SESSION['_mt_idx']]
        );

        if (!$usage_check) {
            $DB->rawQuery("
                INSERT INTO chatbot_usage 
                (mt_idx, first_use_date) 
                VALUES (?, NOW())",
                [$_SESSION['_mt_idx']]
            );
        }

        // 포인트 차감 및 사용 내역 기록 (무료 사용 포함)
        if ($monthly_usage['usage_count'] >= FREE_USAGE_LIMIT) {
            // 포인트 차감
            $DB->rawQuery("
                UPDATE member_t 
                SET mt_point = mt_point - ? 
                WHERE mt_idx = ?",
                [POINT_PER_USAGE, $_SESSION['_mt_idx']]
            );

            // 포인트 사용 내역 기록 (포인트 차감)
            $DB->rawQuery("
                INSERT INTO point_history_t 
                (mt_idx, point_amount, point_type, point_description, created_at) 
                VALUES (?, ?, ?, ?, NOW())",
                [
                    $_SESSION['_mt_idx'], 
                    -POINT_PER_USAGE, 
                    'use', 
                    'AI ' . $category['parent_name'] . ' - ' . $category['ct_name']
                ]
            );
        } else {
            // 무료 사용 내역 기록 (포인트 0)
            $DB->rawQuery("
                INSERT INTO point_history_t 
                (mt_idx, point_amount, point_type, point_description, created_at) 
                VALUES (?, ?, ?, ?, NOW())",
                [
                    $_SESSION['_mt_idx'], 
                    0, 
                    'use', 
                    'AI ' . $category['parent_name'] . ' - ' . $category['ct_name'] . ' (무료)'
                ]
            );
        }

        // 최신 결과 갱신
        $DB->rawQuery("
            UPDATE chat_sessions
            SET last_ai_result = ?, last_ai_result_type = ?
            WHERE cs_idx = ?",
            [$ai_conversation, $is_problem_creation ? 'problem' : 'chat', $cs_idx]
        );

        $DB->commit();

        echo json_encode([
            'success' => true,
            'message' => '문제가 성공적으로 생성되었습니다.',
            'ct_idx' => $categoryId,
            'session_id' => $session_id
        ]);

    } catch (Exception $e) {
        $DB->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
