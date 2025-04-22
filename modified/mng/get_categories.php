<?php
// 출력 버퍼링 시작
ob_start();

// 필요한 include 파일들
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head.inc.php";

// 이전 출력 버퍼 제거
ob_clean();

// JSON 헤더 설정
header('Content-Type: application/json; charset=utf-8');

try {
    $parent_idx = isset($_GET['parent_idx']) ? (int)$_GET['parent_idx'] : 0;

    // 모든 카테고리 조회 (status 조건 제거)
    $categories = $DB->rawQuery("
        SELECT ct_idx, ct_name 
        FROM category_t 
        WHERE parent_idx = ? 
        ORDER BY ct_order", 
        [$parent_idx]
    );

    echo json_encode($categories);

} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}

// 출력 버퍼 플러시
ob_end_flush();
?>