<?php
ob_start();
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head.inc.php";
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $cvIdx = isset($data['cv_idx']) ? (int)$data['cv_idx'] : 0;

    if (!$cvIdx) {
        throw new Exception('챗봇 변수 인덱스가 필요합니다.');
    }

    $DB->startTransaction();

    try {
        $DB->rawQuery("
            DELETE FROM chat_variable_values 
            WHERE cv_idx = ?;",
            [$cvIdx]
        );

        // 관련된 변수 삭제
        $DB->rawQuery("
            DELETE FROM chatbot_variable_t 
            WHERE cv_idx = ?",
            [$cvIdx]
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
