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
    // 필수 파라미터 체크
    if (!isset($_POST['ct_idx'])) {
        throw new Exception('카테고리 ID가 필요합니다.');
    }

    $categoryId = (int)$_POST['ct_idx'];
    $categoryName = $_POST['category_name'] ?? '';
    $promptTitle = $_POST['prompt_title'] ?? '';
    $promptContent = $_POST['prompt_content'] ?? '';
    
    // 변수 배열
    $variableIdxs = $_POST['variable_idx'] ?? [];
    $variableNames = $_POST['variable_name'] ?? [];
    $variableTypes = $_POST['variable_type'] ?? [];
    $variableDescs = $_POST['variable_desc'] ?? [];
    $deletedVariables = !empty($_POST['deleted_variables']) ? explode(',', $_POST['deleted_variables']) : [];

    // 트랜잭션 시작
    $DB->startTransaction(); // beginTransaction() 대신 startTransaction() 사용

    try {
        // 1. 카테고리 정보 업데이트
        $DB->rawQuery("
            UPDATE category_t 
            SET ct_name = ? 
            WHERE ct_idx = ?",
            [$categoryName, $categoryId]
        );

        // 2. 프롬프트 정보 업데이트
        $existingPrompt = $DB->rawQueryOne("
            SELECT cp_idx 
            FROM chatbot_prompt_t 
            WHERE ct_idx = ?",
            [$categoryId]
        );

        if ($existingPrompt) {
            $DB->rawQuery("
                UPDATE chatbot_prompt_t 
                SET cp_title = ?, cp_content = ? 
                WHERE ct_idx = ?",
                [$promptTitle, $promptContent, $categoryId]
            );
        } else {
            $DB->rawQuery("
                INSERT INTO chatbot_prompt_t 
                (ct_idx, cp_title, cp_content, cp_wdate) 
                VALUES (?, ?, ?, NOW())",
                [$categoryId, $promptTitle, $promptContent]
            );
        }

        // 3. 삭제된 변수 처리 (실제 삭제)
        if (!empty($deletedVariables)) {
            $placeholders = str_repeat('?,', count($deletedVariables) - 1) . '?';
            $params = array_merge($deletedVariables, [$categoryId]);
            $DB->rawQuery("
                DELETE FROM chatbot_variable_t 
                WHERE cv_idx IN ($placeholders) AND ct_idx = ?",
                $params
            );
        }

        // 4. 변수 정보 업데이트
        foreach ($variableNames as $index => $name) {
            $idx = $variableIdxs[$index] ?? null;
            $type = $variableTypes[$index] ?? 'text';
            $desc = $variableDescs[$index] ?? '';
            $order = $index + 1;

            if ($idx) {
                // 기존 변수 업데이트
                $DB->rawQuery("
                    UPDATE chatbot_variable_t 
                    SET cv_name = ?, 
                        cv_type = ?, 
                        cv_description = ?, 
                        cv_order = ?
                    WHERE cv_idx = ? AND ct_idx = ?",
                    [$name, $type, $desc, $order, $idx, $categoryId]
                );
            } else {
                // 새 변수 추가
                $DB->rawQuery("
                    INSERT INTO chatbot_variable_t 
                    (ct_idx, cv_name, cv_type, cv_description, cv_order, cv_wdate) 
                    VALUES (?, ?, ?, ?, ?, NOW())",
                    [$categoryId, $name, $type, $desc, $order]
                );
            }
        }

        // 트랜잭션 커밋
        $DB->commit();

        // 성공 응답
        echo json_encode([
            'success' => true,
            'message' => '성공적으로 저장되었습니다.'
        ]);

    } catch (Exception $e) {
        // 롤백
        $DB->rollback();
        throw $e;
    }

} catch (Exception $e) {
    // 에러 응답
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// 출력 버퍼 플러시
ob_end_flush();
?>