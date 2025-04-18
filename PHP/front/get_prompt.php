<?php
header('Content-Type: application/json');
require_once('../config/database.php');

try {
    // 데이터베이스 연결
    $database = Database::getInstance();
    $db = $database->getConnection();

    // GET 또는 POST 요청에서 파라미터 가져오기
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $requestData = json_decode(file_get_contents('php://input'), true);
        $parentCtIdx = $requestData['parent_ct_idx'] ?? null;
        $ctIdx = $requestData['ct_idx'] ?? null;
    } else {
        $parentCtIdx = $_GET['parent_ct_idx'] ?? null;
        $ctIdx = $_GET['ct_idx'] ?? null;
    }

    // 필수 파라미터 체크
    if (!$parentCtIdx || !$ctIdx) {
        throw new Exception('필수 파라미터가 누락되었습니다.');
    }

    // 프롬프트 조회
    $prompt = $db->rawQuery(
        "SELECT cp_idx, cp_title, cp_content 
         FROM chatbot_prompt_t 
         WHERE parent_ct_idx = ? AND ct_idx = ? 
         AND cp_status = 'Y' 
         LIMIT 1",
        [$parentCtIdx, $ctIdx]
    );

    // 변수 조회
    $variables = $db->rawQuery(
        "SELECT cv_idx, cv_name, cv_type, cv_description, cv_options, cv_required 
         FROM chatbot_variable_t 
         WHERE ct_idx = ? 
         AND cv_status = 'Y' 
         ORDER BY cv_order ASC",
        [$ctIdx]
    );

    echo json_encode([
        'success' => true,
        'prompt' => !empty($prompt) ? $prompt[0] : null,
        'variables' => $variables
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
