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
    $requiredPoint = $_POST['required_point'] ?? '10';
    
    // 변수 배열
    $variableIdxs = $_POST['variable_idx'] ?? [];
    $variableNames = $_POST['variable_name'] ?? [];
    $variableTypes = $_POST['variable_type'] ?? [];
    $variableDescs = $_POST['variable_desc'] ?? [];
    $variableRequired = $_POST['variable_required'] ?? [];
    $deletedVariables = !empty($_POST['deleted_variables']) ? explode(',', $_POST['deleted_variables']) : [];

    // 파라미터 추가
    $parentId = $_POST['parent_idx'] ?? '';
    $botDescription = $_POST['bot_description'] ?? '';

    // 필수값 검증에 추가
    if (empty($botDescription)) {
        throw new Exception('챗봇 설명을 입력해주세요.');
    }

    // 트랜잭션 시작
    $DB->startTransaction(); // beginTransaction() 대신 startTransaction() 사용

    try {
        // 1. 카테고리 정보 업데이트
        $DB->rawQuery("
            UPDATE category_t 
            SET ct_name = ?,
                ct_required_point = ?
            WHERE ct_idx = ?",
            [$categoryName, $requiredPoint, $categoryId]
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

        // 3. 삭제된 변수 처리 (상태만 변경)
        if (!empty($deletedVariables)) {
            $placeholders = str_repeat('?,', count($deletedVariables) - 1) . '?';
            $params = array_merge($deletedVariables, [$categoryId]);
            $DB->rawQuery("
                UPDATE chatbot_variable_t 
                SET cv_status = 'N'
                WHERE cv_idx IN ($placeholders) AND ct_idx = ?",
                $params
            );
        }

        // 4. 변수 정보 업데이트
        // 먼저 기존 변수들의 상태를 보존
        $existingVariables = $DB->rawQuery("
            SELECT cv_idx, cv_name, cv_type, cv_description, cv_order
            FROM chatbot_variable_t
            WHERE ct_idx = ?
            ORDER BY cv_order",
            [$categoryId]
        );

        // 기존 변수 ID 배열 생성
        $existingVariableIds = array_column($existingVariables, 'cv_idx');
        
        // 변수 정보 업데이트 및 추가
        foreach ($variableNames as $index => $name) {
            $idx = $variableIdxs[$index] ?? null;
            $type = $variableTypes[$index] ?? 'text';
            $desc = $variableDescs[$index] ?? '';
            $order = $index + 1;
            
            // 필수 여부 설정
            $required = in_array($idx, $variableRequired) ? 'Y' : 'N';
            
            // 선택 타입일 경우 옵션 처리
            $options = null;
            if ($type === 'select') {
                $optionsStr = $_POST['variable_options'][$index] ?? '';
                if (empty($optionsStr)) {
                    throw new Exception('선택형 변수의 옵션을 입력해주세요.');
                }
                // 쉼표로 구분된 옵션을 배열로 변환하고 공백 제거
                $optionsArray = array_map('trim', explode(',', $optionsStr));
                // JSON 형식으로 변환
                $options = json_encode($optionsArray, JSON_UNESCAPED_UNICODE);
            }

            if ($idx && in_array($idx, $existingVariableIds)) {
                // 기존 변수 업데이트
                if ($type === 'select') {
                    $DB->rawQuery("
                        UPDATE chatbot_variable_t 
                        SET cv_name = ?, 
                            cv_type = ?, 
                            cv_description = ?, 
                            cv_order = ?,
                            cv_status = 'Y',
                            cv_options = ?,
                            cv_required = ?
                        WHERE cv_idx = ? AND ct_idx = ?",
                        [$name, $type, $desc, $order, $options, $required, $idx, $categoryId]
                    );
                } else {
                    $DB->rawQuery("
                        UPDATE chatbot_variable_t 
                        SET cv_name = ?, 
                            cv_type = ?, 
                            cv_description = ?, 
                            cv_order = ?,
                            cv_status = 'Y',
                            cv_options = NULL,
                            cv_required = ?
                        WHERE cv_idx = ? AND ct_idx = ?",
                        [$name, $type, $desc, $order, $required, $idx, $categoryId]
                    );
                }
            } else {
                // 새 변수 추가
                if ($type === 'select') {
                    $DB->rawQuery("
                        INSERT INTO chatbot_variable_t 
                        (ct_idx, cv_name, cv_type, cv_description, cv_options, cv_order, cv_status, cv_required, cv_wdate) 
                        VALUES (?, ?, ?, ?, ?, ?, 'Y', ?, NOW())",
                        [$categoryId, $name, $type, $desc, $options, $order, $required]
                    );
                } else {
                    $DB->rawQuery("
                        INSERT INTO chatbot_variable_t 
                        (ct_idx, cv_name, cv_type, cv_description, cv_order, cv_status, cv_required, cv_wdate) 
                        VALUES (?, ?, ?, ?, ?, 'Y', ?, NOW())",
                        [$categoryId, $name, $type, $desc, $order, $required]
                    );
                }
            }
        }

        // 설명 업데이트
        $DB->rawQuery("
            INSERT INTO chatbot_description_t 
            (ct_idx, cd_description, cd_wdate) 
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                cd_description = VALUES(cd_description),
                cd_wdate = NOW()",
            [$parentId, $botDescription]
        );

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