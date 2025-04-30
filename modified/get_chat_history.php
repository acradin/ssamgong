<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib.inc.php";
header('Content-Type: application/json');

// 디버깅: 받은 파라미터 확인
error_log("Received parameters: " . print_r($_GET, true));

// 세션 체크
if (!$_SESSION['_mt_idx']) {
    echo json_encode([
        'success' => false,
        'message' => '로그인이 필요합니다.'
    ]);
    exit;
}

try {
    // 파라미터 체크
    if (!isset($_GET['session_id']) && !isset($_GET['ct_idx'])) {
        throw new Exception('잘못된 접근입니다.');
    }

    $sessionId = isset($_GET['session_id']) ? $_GET['session_id'] : null;
    $ctIdx = isset($_GET['ct_idx']) ? (int)$_GET['ct_idx'] : null;

    // 디버깅: 쿼리 전 세션 정보
    error_log("Looking for session with ID: $sessionId or ct_idx: $ctIdx");

    // 세션 정보 조회
    if ($sessionId) {
        $session = $DB->rawQueryOne("
            SELECT cs.*, ct.ct_name
            FROM chat_sessions cs
            JOIN category_t ct ON cs.ct_idx = ct.ct_idx
            WHERE cs.session_id = ? AND cs.mt_idx = ?",
            [$sessionId, $_SESSION['_mt_idx']]
        );
    } else {
        $session = $DB->rawQueryOne("
            SELECT cs.*, ct.ct_name
            FROM chat_sessions cs
            JOIN category_t ct ON cs.ct_idx = ct.ct_idx
            WHERE cs.ct_idx = ? AND cs.mt_idx = ?
            ORDER BY cs.created_at DESC
            LIMIT 1",
            [$ctIdx, $_SESSION['_mt_idx']]
        );
    }

    // 디버깅: 세션 조회 결과
    error_log("Session query result: " . print_r($session, true));

    if (!$session) {
        throw new Exception('세션을 찾을 수 없습니다.');
    }

    // 채팅 메시지 조회
    $messages = $DB->rawQuery("
        SELECT content, is_bot, created_at
        FROM chat_messages
        WHERE cs_idx = ?
        ORDER BY created_at ASC",
        [$session['cs_idx']]
    );

    // 디버깅: 메시지 조회 결과
    error_log("Messages found: " . count($messages));

    // 메시지 포맷팅
    $formatted_messages = array_map(function($message) {
        return [
            'content' => htmlspecialchars($message['content']),
            'is_bot' => (bool)$message['is_bot'],
            'created_at' => date('Y-m-d H:i:s', strtotime($message['created_at']))
        ];
    }, $messages);

    // 변수 값 조회 및 첫 메시지로 추가
    $variables = $DB->rawQuery("
        SELECT cv.cv_name, cv.cv_type, cvv.value
        FROM chat_variable_values cvv
        JOIN chatbot_variable_t cv ON cvv.cv_idx = cv.cv_idx
        WHERE cvv.cs_idx = ?",
        [$session['cs_idx']]
    );

    if (!empty($variables)) {
        $variableContent = "<strong>입력 정보:</strong><br>";
        foreach ($variables as $var) {
            $value = $var['cv_type'] === 'file' ? "📎 {$var['value']}" : $var['value'];
            $variableContent .= "{$var['cv_name']}: {$value}<br>";
        }
        array_unshift($formatted_messages, [
            'content' => $variableContent,
            'is_bot' => true,
            'created_at' => date('Y-m-d H:i:s', strtotime($session['created_at']))
        ]);
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'history' => $formatted_messages
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_chat_history.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 