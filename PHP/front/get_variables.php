<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    
    if (!isset($_GET['ct_idx'])) {
        throw new Exception('카테고리 ID가 필요합니다.');
    }
    
    $ct_idx = $_GET['ct_idx'];
    
    $query = "SELECT * FROM chatbot_variable_t 
              WHERE ct_idx = ? 
              ORDER BY cv_order ASC";
    
    $variables = $db->rawQuery($query, [$ct_idx]);
    
    echo json_encode([
        'success' => true,
        'variables' => $variables
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
