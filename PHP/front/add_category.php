<?php
require_once '../config/database.php';

// 에러 로깅 설정
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// JSON 헤더 설정
header('Content-Type: application/json');

try {
    // 데이터베이스 연결
    $db = Database::getInstance()->getConnection();
    
    // 요청 데이터 확인
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['parent_idx']) || !isset($data['ct_name'])) {
        throw new Exception('필수 파라미터가 누락되었습니다.');
    }

    // parent_idx가 null인 경우 (상위 카테고리)와 아닌 경우(하위 카테고리)를 구분
    if ($data['parent_idx'] === null) {
        // 상위 카테고리의 최대 순서값 조회
        $maxOrder = $db->rawQuery(
            "SELECT COALESCE(MAX(ct_order), 0) as max_order FROM category_t WHERE parent_idx IS NULL"
        );
    } else {
        // 같은 상위 카테고리 내의 하위 카테고리 최대 순서값 조회
        $maxOrder = $db->rawQuery(
            "SELECT COALESCE(MAX(ct_order), 0) as max_order FROM category_t WHERE parent_idx = ?", 
            [$data['parent_idx']]
        );
    }
    
    $nextOrder = $maxOrder[0]['max_order'] + 1;

    // 새 카테고리 데이터
    $insertData = [
        'parent_idx' => $data['parent_idx'],
        'ct_name' => $data['ct_name'],
        'ct_order' => $nextOrder,
        'ct_status' => 'Y'  // 상태 추가
    ];

    // 카테고리 추가
    $db->insert('category_t', $insertData);
    
    // 실제 삽입된 ID 가져오기
    $ct_idx = $db->getLastInsertId();

    if ($ct_idx) {
        $response = [
            'success' => true,
            'ct_idx' => $ct_idx,
            'message' => '카테고리가 추가되었습니다.'
        ];
    } else {
        throw new Exception('카테고리 추가 실패');
    }

    echo json_encode($response);
    exit;

} catch (Exception $e) {
    error_log("Error in add_category.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    echo json_encode($response);
    exit;
}
