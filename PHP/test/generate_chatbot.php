<?php
require_once __DIR__ . '/../config/database.php';

// 세션 시작
session_start();

// 데이터베이스 연결
$db = Database::getInstance()->getConnection();

try {
    // 요청 검증
    if (!isset($_POST['category_id']) || !is_numeric($_POST['category_id'])) {
        throw new Exception('유효하지 않은 카테고리 ID입니다.');
    }

    if (!isset($_SESSION['_mt_idx'])) {
        throw new Exception('로그인이 필요합니다.');
    }

    $categoryId = (int)$_POST['category_id'];
    $userId = $_SESSION['_mt_idx'];

    // 1. 카테고리 존재 여부 확인
    $category = $db->get('category_t', '*', 'ct_idx = ? AND ct_status = "Y"', [$categoryId]);
    if (empty($category)) {
        throw new Exception('존재하지 않는 카테고리입니다.');
    }

    // 2. 사용자 정보 및 포인트 확인
    $member = $db->get('member_t', '*', 'mt_idx = ?', [$userId]);
    if (empty($member)) {
        throw new Exception('회원 정보를 찾을 수 없습니다.');
    }
    $member = $member[0];

    // 3. 무료 사용 기간 확인
    $usage = $db->get('chatbot_usage', '*', 'mt_idx = ?', [$userId]);
    $isFirstUse = empty($usage);
    $isInFreePeriod = false;

    if ($isFirstUse) {
        // 첫 사용 기록 추가
        $db->insert('chatbot_usage', [
            'mt_idx' => $userId,
            'first_use_date' => date('Y-m-d H:i:s')
        ]);
        $isInFreePeriod = true;
    } else {
        $firstUseDate = new DateTime($usage[0]['first_use_date']);
        $now = new DateTime();
        $diff = $now->diff($firstUseDate);
        $isInFreePeriod = $diff->days < 7;
    }

    // 4. 포인트 확인 및 차감 (무료 기간이 아닌 경우)
    $canUsePoint = true;
    $pointMessage = '';
    $pointCost = 100; // 챗봇 생성 비용

    if ($isInFreePeriod) {
        // 무료 사용 기록 추가
        $remainingDays = 7;
        if (!$isFirstUse) {
            $firstUseDate = new DateTime($usage[0]['first_use_date']);
            $now = new DateTime();
            $diff = $now->diff($firstUseDate);
            $remainingDays = 7 - $diff->days;
        }
        
        $db->insert('point_history_t', [
            'mt_idx' => $userId,
            'point_amount' => 0,
            'point_type' => 'free',
            'point_description' => '챗봇 사용 (무료 사용 기간: 남은 ' . $remainingDays . '일)',
            'created_at' => date('Y-m-d H:i:s'),
            'main_ct_idx' => $category[0]['parent_idx'],
            'ct_idx' => $category[0]['ct_idx']
        ]);
        
        $pointMessage = '무료 사용 기간입니다. (남은 기간: ' . $remainingDays . '일)';
    } else {
        if ($member['mt_point'] < $pointCost) {
            $canUsePoint = false;
            throw new Exception('포인트가 부족합니다. (필요 포인트: ' . $pointCost . ', 보유 포인트: ' . $member['mt_point'] . ')');
        } else {
            // 포인트 차감
            $newPoint = $member['mt_point'] - $pointCost;
            $db->update('member_t', 
                ['mt_point' => $newPoint], 
                'mt_idx = ?', 
                [$userId]
            );
            
            // 포인트 사용 내역 기록
            $db->insert('point_history_t', [
                'mt_idx' => $userId,
                'point_amount' => -$pointCost,
                'point_type' => 'use',
                'point_description' => '챗봇 사용',
                'created_at' => date('Y-m-d H:i:s'),
                'main_ct_idx' => $category[0]['parent_idx'],
                'ct_idx' => $category[0]['ct_idx']
            ]);
            
            $pointMessage = '포인트가 차감되었습니다. (차감: ' . $pointCost . ', 잔여: ' . $newPoint . ')';
        }
    }

    // 5. 해당 카테고리의 변수 목록 조회
    $variables = $db->get('chatbot_variable_t', '*', 
        'ct_idx = ? AND cv_status = "Y" ORDER BY cv_order',
        [$categoryId]
    );

    if (empty($variables)) {
        throw new Exception('해당 카테고리에 설정된 변수가 없습니다.');
    }

    // 5-1. 상위 카테고리 정보 조회
    $parentCategory = $db->get('category_t', '*', 
        'ct_idx = (SELECT parent_idx FROM category_t WHERE ct_idx = ?)',
        [$categoryId]
    );

    if (empty($parentCategory)) {
        throw new Exception('상위 카테고리 정보를 찾을 수 없습니다.');
    }

    // 5-2. 프롬프트 조회
    $prompt = $db->get('chatbot_prompt_t', '*',
        'parent_ct_idx = ? AND ct_idx = ? AND cp_status = "Y"',
        [$parentCategory[0]['ct_idx'], $categoryId]
    );

    if (empty($prompt)) {
        throw new Exception('해당 카테고리의 프롬프트를 찾을 수 없습니다.');
    }

    // 6. 채팅 세션 생성
    $sessionId = uniqid('chat_', true);
    $chatSession = [
        'session_id' => $sessionId,
        'mt_idx' => $userId,
        'ct_idx' => $categoryId,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $db->insert('chat_sessions', $chatSession);
    $csIdx = $db->getLastInsertId('chat_sessions', 'cs_idx');

    // 7. 입력된 변수 값 수집 및 프롬프트 변수 치환
    $inputValues = [];
    $promptContent = $prompt[0]['cp_content'];
    foreach ($variables as $var) {
        $varKey = 'var_' . $var['cv_idx'];
        $value = '';
        
        if ($var['cv_type'] === 'file' && isset($_FILES[$varKey])) {
            $file = $_FILES[$varKey];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $value = '파일명: ' . $file['name'];
            }
        } else if (isset($_POST[$varKey])) {
            $value = $_POST[$varKey];
        }
        
        $inputValues[] = [
            'name' => $var['cv_name'],
            'value' => $value,
            'type' => $var['cv_type']
        ];
        
        // 프롬프트 내 변수 치환
        $promptContent = str_replace('{' . $var['cv_name'] . '}', $value, $promptContent);
    }

    // 이전 대화 내용 가져오기 (재생성 요청인 경우)
    $previousMessages = [];
    if (isset($_POST['session_id'])) {
        $previousMessages = $db->get('chat_messages', '*', 
            'cs_idx = (SELECT cs_idx FROM chat_sessions WHERE session_id = ?) ORDER BY created_at ASC',
            [$_POST['session_id']]
        );
        // 기존 세션 ID 사용
        $sessionId = $_POST['session_id'];
        $csIdx = $db->get('chat_sessions', 'cs_idx', 'session_id = ?', [$sessionId])[0]['cs_idx'];
    }

    // API 요청 데이터 구성
    $apiEndpoint = 'https://api.example.com/v1/chat';
    $apiRequestBody = [
        'session_id' => $sessionId,
        'prompt' => $promptContent,
        'category' => [
            'parent' => $parentCategory[0]['ct_name'],
            'child' => $category[0]['ct_name']
        ]
    ];

    // 이전 대화 내용이 있는 경우 포함
    if (!empty($previousMessages)) {
        $apiRequestBody['conversation_history'] = array_map(function($msg) {
            return [
                'role' => $msg['is_bot'] ? 'assistant' : 'user',
                'content' => $msg['content']
            ];
        }, $previousMessages);
    }

    // 추가 입력이 있는 경우 처리
    if (isset($_POST['additional_input']) && !empty(trim($_POST['additional_input']))) {
        // 추가 입력을 DB에 저장
        $db->insert('chat_messages', [
            'cs_idx' => $csIdx,
            'content' => trim($_POST['additional_input']),
            'is_bot' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // API 요청에 추가 입력 포함
        if (!isset($apiRequestBody['conversation_history'])) {
            $apiRequestBody['conversation_history'] = [];
        }
        $apiRequestBody['conversation_history'][] = [
            'role' => 'user',
            'content' => trim($_POST['additional_input'])
        ];

        // 프롬프트 내용 업데이트
        $promptContent .= "\n\n추가 입력:\n" . trim($_POST['additional_input']);
    }
    // 최초 입력인 경우에만 프롬프트 내용 저장
    else if (!isset($_POST['session_id'])) {
        $db->insert('chat_messages', [
            'cs_idx' => $csIdx,
            'content' => $promptContent,
            'is_bot' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    // 9. 임시 응답 생성 및 저장
    $mockResponse = "이것은 {$category[0]['ct_name']}에 대한 임시 응답입니다.\n\n" .
                   "입력하신 내용을 기반으로 생성된 결과입니다.\n" .
                   "실제 API 연동 시 이 부분이 AI의 응답으로 대체됩니다." .
                   (!empty($previousMessages) ? "\n\n(이전 대화 내용을 포함한 재생성 요청입니다)" : "");

    // AI 응답 저장
    $db->insert('chat_messages', [
        'cs_idx' => $csIdx,
        'content' => $mockResponse,
        'is_bot' => 1,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // HTML 응답 헤더
    header('Content-Type: text/html; charset=utf-8');

    // 결과 페이지 출력
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>입력 결과 확인</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .result-box { 
                border: 1px solid #ddd; 
                padding: 20px; 
                margin-bottom: 20px; 
                border-radius: 5px;
            }
            .success { background-color: #d4edda; }
            .warning { background-color: #fff3cd; }
            .error { background-color: #f8d7da; }
            .variable-list { margin-top: 20px; }
            .variable-item { 
                padding: 10px; 
                border-bottom: 1px solid #eee;
            }
            .point-info {
                margin-top: 20px;
                padding: 10px;
                background-color: ' . ($canUsePoint ? '#d4edda' : '#f8d7da') . ';
                border-radius: 5px;
            }
            .session-info {
                margin-top: 20px;
                padding: 10px;
                background-color: #e2e3e5;
                border-radius: 5px;
                border: 1px solid #d6d8db;
            }
            .prompt-info {
                margin-top: 20px;
                padding: 10px;
                background-color: #e2e3e5;
                border-radius: 5px;
                border: 1px solid #d6d8db;
            }
            .api-info {
                margin-top: 20px;
                padding: 10px;
                background-color: #e2e3e5;
                border-radius: 5px;
                border: 1px solid #d6d8db;
            }
            .response-info {
                margin-top: 20px;
                padding: 10px;
                background-color: #e2e3e5;
                border-radius: 5px;
                border: 1px solid #d6d8db;
            }
            .back-button {
                display: inline-block;
                padding: 10px 20px;
                background-color: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
            .back-button:hover {
                background-color: #0056b3;
            }
            .message {
                margin: 10px 0;
                padding: 10px;
                border-radius: 5px;
            }
            .message.bot {
                background-color: #f8f9fa;
            }
            .message.user {
                background-color: #e9ecef;
            }
            .user-input {
                margin: 20px 0;
                padding: 15px;
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 5px;
            }
            .user-input h3 {
                margin-top: 0;
                margin-bottom: 10px;
            }
            .regenerate-button {
                padding: 10px 20px;
                background-color: #28a745;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            .regenerate-button:hover {
                background-color: #218838;
            }
        </style>
    </head>
    <body>
        <h1>입력 내용 확인</h1>
        <div class="result-box">
            <h2>' . htmlspecialchars($category[0]['ct_name']) . '</h2>
            <p>사용자 ID: ' . $userId . '</p>
            <p>요청 시간: ' . date('Y-m-d H:i:s') . '</p>
            <p>무료 사용 기간: ' . ($isInFreePeriod ? '예' : '아니오') . '</p>
        </div>

        <div class="input-values">
            <h3>입력된 값:</h3>';

    // 일반 POST 데이터와 파일 업로드 정보를 한 번에 처리
    foreach ($variables as $var) {
        $varKey = 'var_' . $var['cv_idx'];
        echo '<p><strong>' . htmlspecialchars($var['cv_name']) . ':</strong> ';
        
        if ($var['cv_type'] === 'file' && isset($_FILES[$varKey])) {
            $file = $_FILES[$varKey];
            if ($file['error'] === UPLOAD_ERR_OK) {
                echo '파일명: ' . htmlspecialchars($file['name']) . ', ';
                echo '크기: ' . number_format($file['size'] / 1024, 2) . ' KB, ';
                echo '타입: ' . htmlspecialchars($file['type']);
            } else {
                echo '파일 업로드 실패 (에러코드: ' . $file['error'] . ')';
            }
        } else if (isset($_POST[$varKey])) {
            echo htmlspecialchars($_POST[$varKey]);
        } else {
            echo '입력값 없음';
        }
        echo '</p>';
    }

    echo '</div>';

    echo '<div class="session-info">';
    echo '<h3>대화 세션 정보</h3>';
    echo '<p><strong>세션 ID:</strong> ' . htmlspecialchars($sessionId) . '</p>';
    echo '<p><strong>생성 시간:</strong> ' . $chatSession['created_at'] . '</p>';
    echo '</div>';

    echo '<div class="prompt-info">';
    echo '<h3>프롬프트 정보</h3>';
    echo '<p><strong>상위 카테고리:</strong> ' . htmlspecialchars($parentCategory[0]['ct_name']) . '</p>';
    echo '<p><strong>하위 카테고리:</strong> ' . htmlspecialchars($category[0]['ct_name']) . '</p>';
    echo '<pre>' . htmlspecialchars($promptContent) . '</pre>';
    echo '</div>';

    echo '<div class="api-info">';
    echo '<h3>API 호출 정보</h3>';
    echo '<p><strong>엔드포인트:</strong> ' . htmlspecialchars($apiEndpoint) . '</p>';
    echo '<p><strong>요청 데이터:</strong></p>';
    echo '<pre>' . htmlspecialchars(json_encode($apiRequestBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
    echo '</div>';

    echo '<div class="response-info">';
    echo '<h3>임시 응답</h3>';
    echo '<pre>' . htmlspecialchars($mockResponse) . '</pre>';
    
    // 대화 기록 표시
    if (!empty($previousMessages) || isset($_POST['additional_input'])) {
        echo '<h3>전체 대화 기록</h3>';
        
        // 이전 대화 내용 표시
        foreach ($previousMessages as $msg) {
            echo '<div class="message ' . ($msg['is_bot'] ? 'bot' : 'user') . '">';
            echo '<strong>' . ($msg['is_bot'] ? 'AI' : '사용자') . ':</strong><br>';
            echo '<pre>' . htmlspecialchars($msg['content']) . '</pre>';
            echo '</div>';
        }

        // 현재 추가된 입력 표시
        if (isset($_POST['additional_input']) && !empty(trim($_POST['additional_input']))) {
            echo '<div class="message user">';
            echo '<strong>사용자 (추가 입력):</strong><br>';
            echo '<pre>' . htmlspecialchars(trim($_POST['additional_input'])) . '</pre>';
            echo '</div>';
            
            // 현재의 AI 응답 표시
            echo '<div class="message bot">';
            echo '<strong>AI:</strong><br>';
            echo '<pre>' . htmlspecialchars($mockResponse) . '</pre>';
            echo '</div>';
        }
    }
    echo '</div>';

    echo '<div class="point-info">';
    if ($isInFreePeriod) {
        echo '무료 사용 기간입니다.';
    } else {
        echo $pointMessage;
    }
    echo '</div>';

    echo '</div>
        <div class="button-group">
            <form method="post" action="' . $_SERVER['PHP_SELF'] . '" style="margin-bottom: 20px;">
                <div class="user-input">
                    <h3>추가 입력</h3>
                    <textarea name="additional_input" rows="4" style="width: 100%; margin-bottom: 10px; padding: 8px;" placeholder="추가로 입력하실 내용이 있다면 여기에 작성해주세요."></textarea>
                </div>
                <input type="hidden" name="category_id" value="' . $categoryId . '">
                <input type="hidden" name="session_id" value="' . $sessionId . '">';
                
    // 모든 입력값을 hidden 필드로 추가
    foreach ($variables as $var) {
        $varKey = 'var_' . $var['cv_idx'];
        if (isset($_POST[$varKey])) {
            echo '<input type="hidden" name="' . $varKey . '" value="' . htmlspecialchars($_POST[$varKey]) . '">';
        }
    }
    
    echo '<div style="display: flex; gap: 10px;">
                <button type="submit" class="regenerate-button" style="flex: 1;">다시 생성하기</button>
                <a href="chatbot_page_test.php?category=' . $categoryId . '" class="back-button" style="flex: 1; text-align: center;">돌아가기</a>
            </div>
            </form>
        </div>
    </body>
    </html>';

} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 