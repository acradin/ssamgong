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
    if (!isset($_GET['ct_idx'])) {
        throw new Exception('잘못된 접근입니다.');
    }

    $ct_idx = (int)$_GET['ct_idx'];
    
    $description = $DB->rawQueryOne("
        SELECT cd_description 
        FROM chatbot_description_t 
        WHERE ct_idx = ? 
        ORDER BY cd_wdate DESC 
        LIMIT 1", 
        [$ct_idx]
    );

    echo json_encode([
        'success' => true,
        'description' => $description['cd_description'] ?? ''
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// 출력 버퍼 플러시
ob_end_flush();
?>