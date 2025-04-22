<?php
// 모든 PHP 에러 출력 방지
error_reporting(0);
ini_set('display_errors', 0);

include $_SERVER['DOCUMENT_ROOT'] . "/mng/head.inc.php";

// JSON 헤더 설정
header('Content-Type: application/json; charset=utf-8');

try {
    // POST 데이터 읽기
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['category_id']) || !isset($data['status'])) {
        throw new Exception('필수 파라미터가 누락되었습니다.');
    }

    $categoryId = (int)$data['category_id'];
    $status = $data['status'];

    // 상태 업데이트
    $result = $DB->rawQuery("
        UPDATE category_t 
        SET ct_status = ? 
        WHERE ct_idx = ?",
        [$status, $categoryId]
    );

    if ($result === false) {
        throw new Exception('상태 업데이트에 실패했습니다.');
    }

    // 출력 버퍼 클리어
    ob_clean();

    // 성공 응답
    echo json_encode([
        'success' => true
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Exception $e) {
    // 출력 버퍼 클리어
    ob_clean();
    
    // 에러 응답
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>