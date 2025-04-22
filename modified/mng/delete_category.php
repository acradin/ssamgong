<?php
ob_start();
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head.inc.php";
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $categoryId = isset($data['category_id']) ? (int)$data['category_id'] : 0;

    if (!$categoryId) {
        throw new Exception('카테고리 ID가 필요합니다.');
    }

    $DB->startTransaction();

    try {
        // 관련된 변수 삭제
        $DB->rawQuery("
            DELETE FROM chatbot_variable_t 
            WHERE ct_idx = ?",
            [$categoryId]
        );

        // 프롬프트 삭제
        $DB->rawQuery("
            DELETE FROM chatbot_prompt_t 
            WHERE ct_idx = ?",
            [$categoryId]
        );

        // 카테고리 삭제
        $DB->rawQuery("
            DELETE FROM category_t 
            WHERE ct_idx = ?",
            [$categoryId]
        );

        $DB->commit();
        echo json_encode(['success' => true, 'message' => '성공적으로 삭제되었습니다.']);

    } catch (Exception $e) {
        $DB->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

ob_end_flush();
?>
