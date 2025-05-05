<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib.inc.php";

$api_url = 'https://f26c-182-228-190-72.ngrok-free.app';

// 세션 체크
if (!$_SESSION['_mt_idx']) {
    echo json_encode([
        'success' => false,
        'message' => '로그인이 필요합니다.'
    ]);
    exit;
}

try {
    // 필수 파라미터 체크
    if (!isset($_POST['session_id']) || !isset($_POST['request']) || trim($_POST['request']) === '') {
        throw new Exception('잘못된 요청입니다.');
    }

    $session_id = $_POST['session_id'];
    $request = trim($_POST['request']);

    // 디버깅을 위한 로그 추가
    error_log("Received session_id: " . $session_id);
    error_log("User ID: " . $_SESSION['_mt_idx']);

    // 세션 정보 조회 전에 쿼리 로깅
    $query = "
        SELECT cs.*, ct.ct_name, parent.ct_name as parent_name, parent.ct_idx as parent_ct_idx
        FROM chat_sessions cs
        JOIN category_t ct ON cs.ct_idx = ct.ct_idx
        JOIN category_t parent ON ct.parent_idx = parent.ct_idx
        WHERE cs.session_id = ? AND cs.mt_idx = ?";
    error_log("Query: " . $query);
    error_log("Parameters: " . $session_id . ", " . $_SESSION['_mt_idx']);

    // 세션 정보 조회
    $session = $DB->rawQueryOne($query, [$session_id, $_SESSION['_mt_idx']]);

    // 조회 결과 로깅
    error_log("Query result: " . print_r($session, true));

    if (!$session) {
        throw new Exception('유효하지 않은 세션입니다. (session_id: ' . $session_id . ')');
    }

    // 포인트 체크
    $point_check = $DB->rawQueryOne("
        SELECT mt_point 
        FROM member_t 
        WHERE mt_idx = ? 
        AND mt_point >= 500",
        [$_SESSION['_mt_idx']]
    );

    if (!$point_check) {
        throw new Exception('포인트가 부족합니다.');
    }

    // API 엔드포인트 결정
    $is_problem_creation = ($session['parent_name'] === '문제 제작');

    if ($is_problem_creation) {
        // 기존 메시지 불러오기
        $messages_raw = $DB->rawQuery("
            SELECT is_bot, content 
            FROM chat_messages 
            WHERE cs_idx = ? 
            ORDER BY created_at DESC 
            LIMIT 6", 
            [$session['cs_idx']]
        );
        $messages_raw = array_reverse($messages_raw);
        $messages = [];
        foreach ($messages_raw as $msg) {
            $role = $msg['is_bot'] ? 'assistant' : 'user';
            $messages[] = [
                'role' => $role,
                'content' => $msg['content']
            ];
        }
        $api_data = [
            'messages' => $messages,
            'user_edit' => $request // 추가 요청 내용
        ];

        // FastAPI 서버로 요청 전송 (edit_problems)
        $ch = curl_init($api_url . '/edit_problems/');
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

        // 리턴값: { "content": ..., "conversation": ... }
        $ai_conversation = $result['conversation'] ?? '';
        $ai_content = $result['content'] ?? '';

    } else {
        // Claude API 호출 (edit_chats)
        $messages_raw = $DB->rawQuery("
            SELECT is_bot, content 
            FROM chat_messages 
            WHERE cs_idx = ? 
            ORDER BY created_at DESC 
            LIMIT 6", 
            [$session['cs_idx']]
        );
        $messages_raw = array_reverse($messages_raw);
        $messages = [];
        foreach ($messages_raw as $msg) {
            $role = $msg['is_bot'] ? 'assistant' : 'user';
            $messages[] = [
                'role' => $role,
                'content' => $msg['content']
            ];
        }
        $api_data = [
            'messages' => $messages,
            'user_edit' => $request
        ];

        // FastAPI 서버로 요청 전송 (edit_chats)
        $ch = curl_init($api_url . '/edit_chats/');
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

        // 리턴값: { "content": ..., "conversation": ... }
        $ai_conversation = $result['conversation'] ?? '';
        $ai_content = $result['content'] ?? '';
    }

    // DB 트랜잭션 시작
    $DB->startTransaction();

    try {
        // 채팅 메시지 저장 (사용자 메시지)
        $DB->rawQuery("
            INSERT INTO chat_messages 
            (cs_idx, content, is_bot, created_at) 
            VALUES (?, ?, 0, NOW())",
            [$session['cs_idx'], $request]
        );

        // 채팅 메시지 저장 (AI 응답)
        $DB->rawQuery("
            INSERT INTO chat_messages 
            (cs_idx, content, is_bot, created_at) 
            VALUES (?, ?, 1, NOW())",
            [$session['cs_idx'], $ai_content]
        );

        // 최신 결과 갱신 (세션 요약)
        $DB->rawQuery("
            UPDATE chat_sessions
            SET last_ai_result = ?, last_ai_result_type = ?
            WHERE cs_idx = ?",
            [$ai_conversation, $is_problem_creation ? 'problem' : 'chat', $session['cs_idx']]
        );

        // 포인트 차감
        $DB->rawQuery("
            UPDATE member_t 
            SET mt_point = mt_point - 500 
            WHERE mt_idx = ?",
            [$_SESSION['_mt_idx']]
        );

        // 포인트 사용 내역 기록
        $DB->rawQuery("
            INSERT INTO point_history_t 
            (mt_idx, point_amount, point_type, point_description, created_at, main_ct_idx, ct_idx) 
            VALUES (?, ?, ?, ?, NOW(), ?, ?)",
            [
                $_SESSION['_mt_idx'],
                -500,
                'use',
                'AI 문제 수정 - ' . $session['parent_name'] . ' ' . $session['ct_name'],
                $session['parent_ct_idx'],
                $session['ct_idx']
            ]
        );

        $DB->commit();

        echo json_encode([
            'success' => true,
            'message' => '추가 요청이 처리되었습니다.'
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