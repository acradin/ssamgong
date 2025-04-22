<?php
// 출력 버퍼링 시작
ob_start();

// 헤더 설정
header('Content-Type: application/json; charset=utf-8');

// 필요한 include 파일들
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head.inc.php";

// 이전 출력 버퍼 제거
ob_clean();

try {
    // 필수 파라미터 체크
    if (!isset($_POST['act']) || $_POST['act'] !== 'create') {
        throw new Exception('잘못된 접근입니다.');
    }

    $botSelect = $_POST['bot_select'] ?? '';
    $botName = $_POST['bot_name'] ?? '';
    $categoryName = $_POST['category_name'] ?? '';
    $promptTitle = $_POST['prompt_title'] ?? '';
    $promptContent = $_POST['prompt_content'] ?? '';
    
    // 변수 배열
    $variableNames = $_POST['variable_name'] ?? [];
    $variableTypes = $_POST['variable_type'] ?? [];
    $variableDescs = $_POST['variable_desc'] ?? [];

    // 파라미터에 설명 추가
    $botDescription = $_POST['bot_description'] ?? '';

    // 필수값 검증
    if (empty($botSelect) && empty($botName)) {
        throw new Exception('챗봇을 선택하거나 새로운 챗봇 이름을 입력해주세요.');
    }
    if (empty($categoryName)) {
        throw new Exception('카테고리 이름을 입력해주세요.');
    }
    if (empty($promptTitle)) {
        throw new Exception('프롬프트 제목을 입력해주세요.');
    }
    if (empty($promptContent)) {
        throw new Exception('프롬프트 내용을 입력해주세요.');
    }
    if (empty($variableNames)) {
        throw new Exception('최소 하나의 변수를 입력해주세요.');
    }
    if (empty($botDescription)) {
        throw new Exception('챗봇 설명을 입력해주세요.');
    }

    // 트랜잭션 시작
    $DB->startTransaction();

    try {
        $parentId = null;

        // 1. 챗봇(부모 카테고리) 처리
        if ($botSelect) {
            // 기존 챗봇 선택
            $parentId = $botSelect;
        } else {
            // 새로운 챗봇 생성
            if (empty($botName)) {
                throw new Exception('챗봇 이름을 입력해주세요.');
            }

            // 최대 순서 값 조회
            $maxOrder = $DB->rawQueryOne("SELECT MAX(ct_order) as max_order FROM category_t WHERE parent_idx IS NULL");
            $nextOrder = ($maxOrder['max_order'] ?? 0) + 1;

            // 새 챗봇 추가
            $DB->rawQuery("INSERT INTO category_t (ct_name, ct_order, ct_status) VALUES (?, ?, 'Y')", 
                [$botName, $nextOrder]);
            $parentId = $DB->getInsertId();
        }

        // 2. 카테고리(자식) 생성
        // 카테고리 중복 체크 추가
        $existingCategory = $DB->rawQueryOne("
            SELECT ct_idx 
            FROM category_t 
            WHERE parent_idx = ? AND ct_name = ? AND ct_status = 'Y'",
            [$parentId, $categoryName]
        );

        if ($existingCategory) {
            throw new Exception('이미 존재하는 카테고리 이름입니다.');
        }

        // 해당 부모 카테고리 아래의 최대 순서값 조회
        $maxOrder = $DB->rawQueryOne("
            SELECT MAX(ct_order) as max_order 
            FROM category_t 
            WHERE parent_idx = ?",
            [$parentId]
        );
        $nextOrder = ($maxOrder['max_order'] ?? 0) + 1;

        // 중복이 없는 경우에만 카테고리 생성 (ct_order 포함)
        $DB->rawQuery("
            INSERT INTO category_t 
            (ct_name, parent_idx, ct_status, ct_order) 
            VALUES (?, ?, 'Y', ?)",
            [$categoryName, $parentId, $nextOrder]
        );
        $categoryId = $DB->getInsertId();

        // 3. 프롬프트 생성 - cp_wdate 추가 및 parent_ct_idx 포함
        $DB->rawQuery("
            INSERT INTO chatbot_prompt_t 
            (parent_ct_idx, ct_idx, cp_title, cp_content, cp_wdate) 
            VALUES (?, ?, ?, ?, NOW())",
            [$parentId, $categoryId, $promptTitle, $promptContent]
        );

        // 4. 변수 생성
        foreach ($variableNames as $index => $name) {
            if (empty($name)) continue;
            
            $type = $variableTypes[$index] ?? 'text';
            $desc = $variableDescs[$index] ?? '';
            $order = $index + 1;
            
            // 선택 타입일 경우 옵션을 JSON 배열로 변환
            if ($type === 'select') {
                $options = $_POST['variable_options'][$index] ?? '';
                // 쉼표로 구분된 옵션을 배열로 변환하고 공백 제거
                $optionsArray = array_map('trim', explode(',', $options));
                // JSON 형식으로 변환
                $optionsJson = json_encode($optionsArray, JSON_UNESCAPED_UNICODE);
                
                $DB->rawQuery("INSERT INTO chatbot_variable_t 
                    (ct_idx, cv_name, cv_type, cv_description, cv_options, cv_order) 
                    VALUES (?, ?, ?, ?, ?, ?)",
                    [$categoryId, $name, $type, $desc, $optionsJson, $order]);
            } else {
                $DB->rawQuery("INSERT INTO chatbot_variable_t 
                    (ct_idx, cv_name, cv_type, cv_description, cv_order) 
                    VALUES (?, ?, ?, ?, ?)",
                    [$categoryId, $name, $type, $desc, $order]);
            }
        }

        // 챗봇 생성 후 설명 저장 로직 추가 (트랜잭션 내부)
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
        
        echo json_encode([
            'success' => true,
            'message' => '성공적으로 저장되었습니다.'
        ]);

    } catch (Exception $e) {
        $DB->rollback();
        throw new Exception($e->getMessage());
    }

} catch (Exception $e) {
    if (isset($DB)) {
        $DB->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// 출력 버퍼 플러시
ob_end_flush();
?>
