<?php
// 모든 PHP 에러 출력 방지
error_reporting(0);
ini_set('display_errors', 0);

include $_SERVER['DOCUMENT_ROOT'] . "/mng/head.inc.php";

// JSON 헤더 설정
header('Content-Type: application/json; charset=utf-8');

try {
    // 파라미터 체크
    if (!isset($_GET['category_id'])) {
        throw new Exception('카테고리 ID가 필요합니다.');
    }

    $categoryId = (int)$_GET['category_id'];
    
    // 디버깅용 로그
    error_log("Requested category ID: " . $categoryId);
    
    // 카테고리와 부모 카테고리 정보 함께 조회
    $category = $DB->rawQueryOne("
        SELECT c.ct_idx, c.ct_name, c.ct_status,
               p.ct_idx as parent_idx, p.ct_name as parent_name, p.ct_status as parent_status
        FROM category_t c
        LEFT JOIN category_t p ON c.parent_idx = p.ct_idx
        WHERE c.ct_idx = ?", 
        [$categoryId]
    );

    if (!$category) {
        throw new Exception('카테고리를 찾을 수 없습니다.');
    }

    // 프롬프트 정보 조회
    $prompt = $DB->rawQueryOne("
        SELECT cp_title, cp_content
        FROM chatbot_prompt_t
        WHERE ct_idx = ?",
        [$categoryId]
    );

    // 변수 정보 조회
    $variables = $DB->rawQuery("
        SELECT cv_idx, cv_name, cv_type, cv_description, cv_options
        FROM chatbot_variable_t
        WHERE ct_idx = ? AND cv_status = 'Y'
        ORDER BY cv_order",
        [$categoryId]
    );

    // 챗봇 설명 조회
    $botDescription = $DB->rawQueryOne("
        SELECT cd_description 
        FROM chatbot_description_t 
        WHERE ct_idx = (
            SELECT parent_idx 
            FROM category_t 
            WHERE ct_idx = ?
        )
        ORDER BY cd_wdate DESC 
        LIMIT 1",
        [$categoryId]
    );

    // 결과 반환
    $result = [
        'success' => true,
        'category' => [
            'ct_idx' => $category['ct_idx'],
            'ct_name' => $category['ct_name'],
            'ct_status' => $category['ct_status']
        ],
        'parent_category' => [
            'ct_idx' => $category['parent_idx'],
            'ct_name' => $category['parent_name'],
            'ct_status' => $category['parent_status']
        ],
        'prompt' => $prompt ?: null,
        'variables' => $variables ?: [],
        'bot_description' => $botDescription['cd_description'] ?? ''
    ];

    // 출력 버퍼 클리어
    ob_clean();
    
    // JSON 인코딩 및 출력
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
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
