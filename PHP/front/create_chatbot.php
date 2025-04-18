<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

require_once __DIR__ . '/../config/database.php';

try {
    // 데이터 받기
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON 디코딩 오류: ' . json_last_error_msg());
    }

    // 데이터베이스 연결
    $db = Database::getInstance()->getConnection();

    try {
        // 1. 상위 카테고리(챗봇) 추가
        $maxOrderQuery = "SELECT COALESCE(MAX(ct_order), 0) as max_order FROM category_t WHERE parent_idx IS NULL";
        $maxOrder = $db->rawQuery($maxOrderQuery);
        $nextOrder = $maxOrder[0]['max_order'] + 1;

        $parentCategoryData = [
            'parent_idx' => null,
            'ct_name' => $data['bot_name'],
            'ct_order' => $nextOrder,
            'ct_status' => 'Y'
        ];
        
        $db->insert('category_t', $parentCategoryData);
        $parentCategoryId = $db->getLastInsertId('category_t', 'ct_idx');

        // 2. 하위 카테고리 추가
        $selectedSubCategoryId = null;
        $maxSubOrderQuery = "SELECT COALESCE(MAX(ct_order), 0) as max_order FROM category_t WHERE parent_idx = ?";
        $maxSubOrder = $db->rawQuery($maxSubOrderQuery, [$parentCategoryId]);
        $nextSubOrder = $maxSubOrder[0]['max_order'] + 1;

        foreach ($data['categories'] as $index => $category) {
            $subCategoryData = [
                'parent_idx' => $parentCategoryId,
                'ct_name' => $category['name'],
                'ct_order' => $nextSubOrder++,
                'ct_status' => 'Y'
            ];
            
            $db->insert('category_t', $subCategoryData);
            $subCategoryId = $db->getLastInsertId('category_t', 'ct_idx');

            // 선택된 하위 카테고리 ID 저장
            if ($category['is_selected']) {
                $selectedSubCategoryId = $subCategoryId;
            }
        }

        // 3. 선택된 하위 카테고리에 프롬프트 추가
        if ($selectedSubCategoryId) {
            $promptData = [
                'parent_ct_idx' => $parentCategoryId,  // 상위 카테고리 ID
                'ct_idx' => $selectedSubCategoryId,    // 선택된 하위 카테고리 ID
                'cp_title' => $data['prompt']['cp_title'],
                'cp_content' => $data['prompt']['cp_content'],
                'cp_status' => 'Y',
                'cp_wdate' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('chatbot_prompt_t', $promptData);

            // 4. 선택된 하위 카테고리에 변수 추가
            foreach ($data['variables'] as $varIndex => $variable) {
                $variableData = [
                    'ct_idx' => $selectedSubCategoryId,  // 선택된 하위 카테고리 ID
                    'mt_idx' => $_SESSION['_mt_idx'] ?? 2,  // 테스트용 관리자 ID
                    'cv_name' => $variable['cv_name'],
                    'cv_type' => $variable['cv_type'],
                    'cv_description' => $variable['cv_description'],
                    'cv_order' => $varIndex + 1,
                    'cv_required' => 'Y',
                    'cv_status' => 'Y',
                    'cv_wdate' => date('Y-m-d H:i:s')
                ];
                
                $db->insert('chatbot_variable_t', $variableData);
            }
        }

        echo json_encode(['success' => true, 'message' => '챗봇이 성공적으로 생성되었습니다.']);

    } catch (Exception $e) {
        throw $e;
    }

} catch (Exception $e) {
    error_log('Error in create_chatbot.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '저장 중 오류가 발생했습니다: ' . $e->getMessage()]);
}
?>
