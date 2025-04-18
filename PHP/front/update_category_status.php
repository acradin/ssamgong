<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['category_id']) || !isset($input['status'])) {
        throw new Exception('필수 파라미터가 누락되었습니다.');
    }

    $db = Database::getInstance()->getConnection();
    
    $query = "UPDATE category_t SET ct_status = ? WHERE ct_idx = ?";
    $result = $db->rawQuery($query, [$input['status'], $input['category_id']]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}