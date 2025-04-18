<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['parent_id'])) {
        throw new Exception('부모 카테고리 ID가 필요합니다.');
    }

    $db = Database::getInstance()->getConnection();
    
    $query = "SELECT ct_idx, ct_name, ct_status, ct_order 
              FROM category_t 
              WHERE parent_idx = ? 
              ORDER BY ct_order ASC";
    
    $categories = $db->rawQuery($query, [$_GET['parent_id']]);
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
