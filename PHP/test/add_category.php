<?php
require_once __DIR__ . '/../config/database.php';

// 세션 시작
session_start();

// 테스트용 관리자 ID 설정
$_SESSION['_mt_idx'] = 2;  // 관리자 계정의 mt_idx

// 데이터베이스 연결
$db = Database::getInstance()->getConnection();

// 상위 카테고리 ID 가져오기 (있는 경우)
$parentId = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : null;

try {
    // 관리자 권한 확인
    $admin = $db->get('member_t', '*', 'mt_idx = ? AND mt_level = 9', [$_SESSION['_mt_idx']]);
    if (empty($admin)) {
        throw new Exception('관리자 권한이 없습니다.');
    }

    // POST 요청 처리
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['category_name'])) {
            throw new Exception('카테고리 이름은 필수입니다.');
        }

        // 현재 최대 순서 값 조회
        $maxOrder = $db->get('category_t', 'MAX(ct_order) as max_order', 
            $parentId ? 'parent_idx = ?' : 'parent_idx IS NULL', 
            $parentId ? [$parentId] : []
        );
        $newOrder = ($maxOrder[0]['max_order'] ?? 0) + 1;

        // 카테고리 추가
        $categoryData = [
            'parent_idx' => $parentId,
            'ct_name' => $_POST['category_name'],
            'ct_order' => $newOrder,
            'ct_status' => 'Y'
        ];
        $db->insert('category_t', $categoryData);
        $categoryId = $db->getLastInsertId('category_t', 'ct_idx');

        // 하위 카테고리인 경우 프롬프트와 변수 추가
        if ($parentId) {
            // 프롬프트 추가
            if (!empty($_POST['prompt_title']) && !empty($_POST['prompt_content'])) {
                $promptData = [
                    'parent_ct_idx' => $parentId,
                    'ct_idx' => $categoryId,
                    'cp_title' => $_POST['prompt_title'],
                    'cp_content' => $_POST['prompt_content'],
                    'cp_status' => 'Y',
                    'cp_wdate' => date('Y-m-d H:i:s')
                ];
                $db->insert('chatbot_prompt_t', $promptData);
            }

            // 변수 추가
            if (!empty($_POST['variables'])) {
                foreach ($_POST['variables'] as $var) {
                    if (!empty($var['name'])) {
                        $varData = [
                            'ct_idx' => $categoryId,
                            'mt_idx' => $_SESSION['_mt_idx'],
                            'cv_name' => $var['name'],
                            'cv_description' => $var['description'] ?? '',
                            'cv_type' => $var['type'] ?? 'text',
                            'cv_options' => !empty($var['options']) ? json_encode(explode(',', $var['options'])) : null,
                            'cv_required' => $var['required'] ?? 'Y',
                            'cv_order' => $var['order'] ?? 1,
                            'cv_status' => 'Y',
                            'cv_wdate' => date('Y-m-d H:i:s')
                        ];
                        $db->insert('chatbot_variable_t', $varData);
                    }
                }
            }
        }

        // 성공 후 목록 페이지로 리다이렉트
        header('Location: admin_categories.php');
        exit;
    }

    // 상위 카테고리 정보 조회 (하위 카테고리 추가 시)
    $parentCategory = null;
    if ($parentId) {
        $parentCategory = $db->get('category_t', '*', 'ct_idx = ?', [$parentId]);
        if (empty($parentCategory)) {
            throw new Exception('상위 카테고리를 찾을 수 없습니다.');
        }
        $parentCategory = $parentCategory[0];
    }

    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>카테고리 추가</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px; 
                background-color: #f5f5f5;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            h1 { 
                color: #333;
                margin-bottom: 30px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
                color: #495057;
            }
            input[type="text"], select, textarea {
                width: 100%;
                padding: 8px;
                border: 1px solid #ced4da;
                border-radius: 4px;
                box-sizing: border-box;
            }
            .variable-form {
                border: 1px solid #dee2e6;
                padding: 15px;
                margin-bottom: 15px;
                border-radius: 4px;
                background-color: #f8f9fa;
            }
            .btn {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 1em;
            }
            .btn-primary {
                background-color: #007bff;
                color: white;
            }
            .btn-secondary {
                background-color: #6c757d;
                color: white;
            }
            .btn-add-variable {
                background-color: #28a745;
                color: white;
                margin-bottom: 20px;
            }
            .admin-info {
                background-color: #e9ecef;
                padding: 10px 15px;
                border-radius: 4px;
                margin-bottom: 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .admin-info span {
                color: #495057;
            }
            .admin-info .admin-level {
                background-color: #007bff;
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 0.9em;
            }
            #variableContainer {
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="admin-info">
                <span>관리자: ' . htmlspecialchars($admin[0]['mt_nickname']) . ' (ID: ' . htmlspecialchars($admin[0]['mt_id']) . ')</span>
                <span class="admin-level">관리자 레벨: ' . $admin[0]['mt_level'] . '</span>
            </div>
            <h1>' . ($parentId ? '하위 카테고리 추가' : '상위 카테고리 추가') . '</h1>';
            
    if ($parentId) {
        echo '<p>상위 카테고리: ' . htmlspecialchars($parentCategory['ct_name']) . '</p>';
    }

    echo '<form method="post" action="">
            <div class="form-group">
                <label for="category_name">카테고리 이름 <span style="color: red;">*</span></label>
                <input type="text" id="category_name" name="category_name" required>
            </div>';

    // 하위 카테고리인 경우 프롬프트와 변수 입력 폼 추가
    if ($parentId) {
        echo '<h2>프롬프트 설정</h2>
              <div class="form-group">
                  <label for="prompt_title">프롬프트 제목 <span style="color: red;">*</span></label>
                  <input type="text" id="prompt_title" name="prompt_title" required>
              </div>
              <div class="form-group">
                  <label for="prompt_content">프롬프트 내용 <span style="color: red;">*</span></label>
                  <textarea id="prompt_content" name="prompt_content" rows="5" required 
                    placeholder="프롬프트 내용을 입력하세요. 변수는 {변수명} 형식으로 입력하세요.&#10;예시: {교과}에서 {활동 내용}을 수행했습니다."></textarea>
              </div>
              <h2>입력 변수 설정</h2>
              <button type="button" class="btn btn-add-variable" onclick="addVariable()">변수 추가</button>
              <div id="variableContainer"></div>';
    }

    echo '<div class="form-group" style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">저장</button>
            <button type="button" class="btn btn-secondary" onclick="location.href=\'admin_categories.php\'">취소</button>
          </div>
        </form>
        <script>
        function addVariable() {
            const container = document.getElementById("variableContainer");
            const index = container.children.length;
            
            const varForm = document.createElement("div");
            varForm.className = "variable-form";
            varForm.innerHTML = `
                <input type="hidden" name="variables[${index}][order]" value="${index + 1}">
                <div class="form-group">
                    <label>변수명 <span style="color: red;">*</span></label>
                    <input type="text" name="variables[${index}][name]" required>
                </div>
                <div class="form-group">
                    <label>설명</label>
                    <textarea name="variables[${index}][description]" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>타입</label>
                    <select name="variables[${index}][type]" onchange="toggleOptions(this)">
                        <option value="text">텍스트</option>
                        <option value="select">선택</option>
                        <option value="date">날짜</option>
                        <option value="file">파일</option>
                    </select>
                </div>
                <div class="form-group options-group" style="display: none;">
                    <label>옵션 (쉼표로 구분)</label>
                    <input type="text" name="variables[${index}][options]" placeholder="옵션1,옵션2,옵션3">
                </div>
                <div class="form-group">
                    <label>필수 여부</label>
                    <select name="variables[${index}][required]">
                        <option value="Y">예</option>
                        <option value="N">아니오</option>
                    </select>
                </div>
            `;
            container.appendChild(varForm);
        }

        function toggleOptions(select) {
            const optionsGroup = select.parentElement.parentElement.querySelector(".options-group");
            optionsGroup.style.display = select.value === "select" ? "block" : "none";
        }
        </script>
        </div>
    </body>
    </html>';

} catch (Exception $e) {
    echo '<div class="error-message">오류 발생: ' . htmlspecialchars($e->getMessage()) . '</div>';
} 