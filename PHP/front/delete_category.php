<?php
require_once '../config/database.php';

// 디버깅을 위해 에러 표시 활성화
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 로그 파일에 에러 기록
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

header('Content-Type: application/json');

try {
    // 입력 데이터 로깅
    error_log("Received data: " . file_get_contents('php://input'));
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['ct_idx'])) {
        throw new Exception('카테고리 ID가 필요합니다.');
    }

    // 데이터베이스 연결
    $db = Database::getInstance()->getConnection();

    // 1. 먼저 해당 카테고리의 변수 삭제
    $db->rawQuery(
        "DELETE FROM chatbot_variable_t WHERE ct_idx = ?",
        [$data['ct_idx']]
    );

    // 2. 해당 카테고리의 프롬프트 삭제
    $db->rawQuery(
        "DELETE FROM chatbot_prompt_t WHERE ct_idx = ? OR parent_ct_idx = ?",
        [$data['ct_idx'], $data['ct_idx']]
    );

    // 3. 마지막으로 카테고리 삭제
    $db->rawQuery(
        "DELETE FROM category_t WHERE ct_idx = ?",
        [$data['ct_idx']]
    );

    echo json_encode([
        'success' => true,
        'message' => '카테고리가 성공적으로 삭제되었습니다.'
    ]);

} catch (Exception $e) {
    // 에러 로깅
    error_log("Error in delete_category.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => '카테고리 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
    ]);
}
