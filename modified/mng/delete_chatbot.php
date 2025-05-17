<?php
ob_start();
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head.inc.php";
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $chatbotId = isset($data['chatbot_id']) ? (int)$data['chatbot_id'] : 0;

    if (!$chatbotId) {
        throw new Exception('챗봇 ID가 필요합니다.');
    }

    $DB->startTransaction();

    try {
        // 1. 하위 카테고리 ID들 조회
        $subCategories = $DB->rawQuery("
            SELECT ct_idx 
            FROM category_t 
            WHERE parent_idx = ?", 
            [$chatbotId]
        );
        
        $subCategoryIds = array_column($subCategories, 'ct_idx');
        
        if (!empty($subCategoryIds)) {
            // 서브 카테고리 ID들을 문자열로 변환
            $subCategoryIdsStr = implode(',', $subCategoryIds);

            // 2. chat_messages 삭제
            $DB->rawQuery("
                DELETE FROM chat_messages 
                WHERE cs_idx IN (
                    SELECT cs_idx 
                    FROM chat_sessions 
                    WHERE ct_idx IN ($subCategoryIdsStr)
                )"
            );

            // 3. chat_variable_values 삭제 (chat_sessions 참조)
            $DB->rawQuery("
                DELETE FROM chat_variable_values 
                WHERE cs_idx IN (
                    SELECT cs_idx 
                    FROM chat_sessions 
                    WHERE ct_idx IN ($subCategoryIdsStr)
                )"
            );

            // 4. chat_variable_values 삭제 (chatbot_variable_t 참조)
            $DB->rawQuery("
                DELETE FROM chat_variable_values 
                WHERE cv_idx IN (
                    SELECT cv_idx 
                    FROM chatbot_variable_t 
                    WHERE ct_idx IN ($subCategoryIdsStr)
                )"
            );

            // 5. chat_sessions 삭제
            $DB->rawQuery("
                DELETE FROM chat_sessions 
                WHERE ct_idx IN ($subCategoryIdsStr)"
            );

            // 6. chatbot_variable_t 삭제
            $DB->rawQuery("
                DELETE FROM chatbot_variable_t 
                WHERE ct_idx IN ($subCategoryIdsStr)"
            );

            // 7. chatbot_prompt_t 삭제
            $DB->rawQuery("
                DELETE FROM chatbot_prompt_t 
                WHERE ct_idx IN ($subCategoryIdsStr) 
                OR parent_ct_idx IN ($subCategoryIdsStr)"
            );

            // 8. point_history_t 삭제
            $DB->rawQuery("
                DELETE FROM point_history_t 
                WHERE ct_idx IN ($subCategoryIdsStr) 
                OR main_ct_idx IN ($subCategoryIdsStr)"
            );

            // 9. chatbot_history_t 삭제
            $DB->rawQuery("
                DELETE FROM chatbot_history_t 
                WHERE ct_idx IN ($subCategoryIdsStr) 
                OR main_ct_idx IN ($subCategoryIdsStr)"
            );

            // 10. chatbot_description_t 삭제
            $DB->rawQuery("
                DELETE FROM chatbot_description_t 
                WHERE ct_idx IN ($subCategoryIdsStr)"
            );

            // 11. 하위 카테고리 삭제
            $DB->rawQuery("
                DELETE FROM category_t 
                WHERE ct_idx IN ($subCategoryIdsStr)"
            );
        }

        // 12. 챗봇 설명 삭제
        $DB->rawQuery("
            DELETE FROM chatbot_description_t 
            WHERE ct_idx = ?", 
            [$chatbotId]
        );

        // 13. 마지막으로 챗봇(상위 카테고리) 삭제
        $DB->rawQuery("
            DELETE FROM category_t 
            WHERE ct_idx = ?", 
            [$chatbotId]
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