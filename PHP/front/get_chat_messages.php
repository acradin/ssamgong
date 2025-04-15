<?php
require_once '../config/database.php';

// AJAX 요청 확인
if (!isset($_GET['cs_idx'])) {
    echo json_encode(['success' => false, 'message' => 'Session ID is required']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // 채팅 메시지 조회
    $query = "SELECT 
        cm.content,
        cm.is_bot,
        cm.created_at
    FROM chat_messages cm
    WHERE cm.cs_idx = ?
    ORDER BY cm.created_at ASC";
    
    $messages = $db->rawQuery($query, [$_GET['cs_idx']]);
    
    // 메시지 포맷팅
    $formatted_messages = array_map(function($message) {
        return [
            'content' => htmlspecialchars($message['content']),
            'is_bot' => (bool)$message['is_bot'],
            'created_at' => date('Y-m-d H:i:s', strtotime($message['created_at']))
        ];
    }, $messages);
    
    echo json_encode([
        'success' => true,
        'messages' => $formatted_messages
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}