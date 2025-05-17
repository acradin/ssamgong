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
        // 1. chat_messages 삭제
        $DB->rawQuery("
            DELETE FROM chat_messages 
            WHERE cs_idx IN (
                SELECT cs_idx 
                FROM chat_sessions 
                WHERE ct_idx = ?
            )",
            [$categoryId]
        );

        // 2. chat_variable_values 삭제 (chat_sessions와 chatbot_variable_t 모두 참조)
        $DB->rawQuery("
            DELETE FROM chat_variable_values 
            WHERE cs_idx IN (
                SELECT cs_idx 
                FROM chat_sessions 
                WHERE ct_idx = ?
            )",
            [$categoryId]
        );

        // 3. chat_variable_values 삭제 (chatbot_variable_t 참조)
        $DB->rawQuery("
            DELETE FROM chat_variable_values 
            WHERE cv_idx IN (
                SELECT cv_idx 
                FROM chatbot_variable_t 
                WHERE ct_idx = ?
            )",
            [$categoryId]
        );

        // 4. chat_sessions 삭제
        $DB->rawQuery("
            DELETE FROM chat_sessions 
            WHERE ct_idx = ?",
            [$categoryId]
        );

        // 5. chatbot_variable_t 테이블의 데이터 삭제
        $DB->rawQuery("
            DELETE FROM chatbot_variable_t 
            WHERE ct_idx = ?",
            [$categoryId]
        );

        // 6. 프롬프트 삭제
        $DB->rawQuery("
            DELETE FROM chatbot_prompt_t 
            WHERE ct_idx = ?",
            [$categoryId]
        );

        // 7. 마지막으로 카테고리 삭제
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
