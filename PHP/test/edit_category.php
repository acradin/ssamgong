<?php
require_once __DIR__ . '/../config/database.php';

// 세션 시작
session_start();
$_SESSION['_mt_idx'] = 2;  // 테스트용 관리자 ID

// 데이터베이스 연결
$db = Database::getInstance()->getConnection();

$error_message = '';
$success_message = '';

try {
    // 관리자 권한 확인
    $admin = $db->get('member_t', '*', 'mt_idx = ? AND mt_level = 9', [$_SESSION['_mt_idx']]);
    if (empty($admin)) {
        throw new Exception('관리자 권한이 없습니다.');
    }

    // 카테고리 ID 확인
    if (!isset($_GET['category_id'])) {
        throw new Exception('카테고리 ID가 필요합니다.');
    }

    $categoryId = (int)$_GET['category_id'];

    // 카테고리 정보 조회
    $category = $db->get('category_t', '*', 'ct_idx = ?', [$categoryId]);
    if (empty($category)) {
        throw new Exception('존재하지 않는 카테고리입니다.');
    }
    $category = $category[0];

    // 상위 카테고리 ID 조회
    $parentId = $category['parent_idx'];
    if (!$parentId) {
        throw new Exception('상위 카테고리가 없는 카테고리입니다.');
    }

    // 프롬프트 정보 조회
    $prompt = $db->get('chatbot_prompt_t', '*', 'ct_idx = ? AND cp_status = "Y"', [$categoryId]);
    $prompt = !empty($prompt) ? $prompt[0] : null;

    // 변수 정보 조회
    $variables = $db->get('chatbot_variable_t', '*', 'ct_idx = ? AND cv_status = "Y" ORDER BY cv_order', [$categoryId]);

    // POST 요청 처리
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // 프롬프트 업데이트
            $promptTitle = $_POST['prompt_title'] ?? '';
            $promptContent = $_POST['prompt_content'] ?? '';

            if ($prompt) {
                // 기존 프롬프트 업데이트
                $db->update('chatbot_prompt_t',
                    [
                        'cp_title' => $promptTitle,
                        'cp_content' => $promptContent,
                        'cp_wdate' => date('Y-m-d H:i:s')
                    ],
                    'ct_idx = ?',
                    [$categoryId]
                );
            } else {
                // 새 프롬프트 추가
                $db->insert('chatbot_prompt_t', [
                    'parent_ct_idx' => $parentId,
                    'ct_idx' => $categoryId,
                    'cp_title' => $promptTitle,
                    'cp_content' => $promptContent,
                    'cp_status' => 'Y',
                    'cp_wdate' => date('Y-m-d H:i:s')
                ]);
            }

            // 변수 업데이트
            if (isset($_POST['variables'])) {
                foreach ($_POST['variables'] as $idx => $variable) {
                    $varId = $variable['id'] ?? null;
                    $varData = [
                        'cv_name' => $variable['name'],
                        'cv_description' => $variable['description'],
                        'cv_type' => $variable['type'],
                        'cv_required' => $variable['required'] ?? 'N',
                        'cv_order' => ($idx + 1),
                        'cv_status' => 'Y'
                    ];

                    if ($varId) {
                        // 기존 변수 업데이트
                        $db->update('chatbot_variable_t', $varData, 'cv_idx = ?', [$varId]);
                    } else {
                        // 새 변수 추가
                        $varData['ct_idx'] = $categoryId;
                        $varData['mt_idx'] = $_SESSION['_mt_idx'];
                        $varData['cv_wdate'] = date('Y-m-d H:i:s');
                        $db->insert('chatbot_variable_t', $varData);
                    }
                }
            }

            $success_message = "변경사항이 성공적으로 저장되었습니다.";
            
            // admin_categories.php로 리다이렉트
            header('Location: admin_categories.php?success=1');
            exit;

        } catch (Exception $e) {
            $error_message = "저장 중 오류가 발생했습니다: " . $e->getMessage();
        }
    }

    // HTML 출력
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>카테고리 편집</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px;
                background-color: #f5f5f5;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            h1, h2 { 
                color: #333;
                margin-bottom: 20px;
            }
            .form-section {
                margin-bottom: 30px;
                padding: 20px;
                background-color: #f8f9fa;
                border-radius: 6px;
            }
            .form-group {
                margin-bottom: 15px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
                color: #495057;
            }
            input[type="text"], textarea, select {
                width: 100%;
                padding: 8px;
                border: 1px solid #ced4da;
                border-radius: 4px;
                box-sizing: border-box;
            }
            textarea {
                min-height: 100px;
            }
            .variable-item {
                background-color: white;
                padding: 15px;
                margin-bottom: 10px;
                border: 1px solid #dee2e6;
                border-radius: 4px;
            }
            .btn-add-variable {
                background-color: #28a745;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                margin-top: 10px;
            }
            .btn-add-variable:hover {
                background-color: #218838;
            }
            .btn-save {
                background-color: #007bff;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 1.1em;
            }
            .btn-save:hover {
                background-color: #0056b3;
            }
            .btn-back {
                background-color: #6c757d;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                margin-right: 10px;
            }
            .btn-back:hover {
                background-color: #5a6268;
            }
            .btn-remove {
                background-color: #dc3545;
                color: white;
                padding: 5px 10px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 0.9em;
                float: right;
            }
            .btn-remove:hover {
                background-color: #c82333;
            }
            .error-message {
                background-color: #f8d7da;
                color: #721c24;
                padding: 15px;
                border-radius: 4px;
                margin-bottom: 20px;
            }
            .success-message {
                background-color: #d4edda;
                color: #155724;
                padding: 15px;
                border-radius: 4px;
                margin-bottom: 20px;
            }
            .buttons-container {
                margin-top: 30px;
                text-align: right;
            }
            .variable-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }
            .required-toggle {
                margin-top: 10px;
            }
            .required-toggle label {
                display: inline;
                margin-left: 5px;
            }
        </style>
    </head>
    <body>
        <div class="container">';

    // 성공/에러 메시지 출력
    if (isset($_GET['success']) && $_GET['success'] == '1') {
        echo '<div class="success-message">변경사항이 성공적으로 저장되었습니다.</div>';
    }
    if ($error_message) {
        echo '<div class="error-message">' . htmlspecialchars($error_message) . '</div>';
    }

    echo '<h1>' . htmlspecialchars($category['ct_name']) . ' 카테고리 편집</h1>
          <form method="post">
            <div class="form-section">
                <h2>프롬프트 설정</h2>
                <div class="form-group">
                    <label for="prompt_title">프롬프트 제목</label>
                    <input type="text" id="prompt_title" name="prompt_title" value="' . htmlspecialchars($prompt['cp_title'] ?? '') . '" required>
                </div>
                <div class="form-group">
                    <label for="prompt_content">프롬프트 내용</label>
                    <textarea id="prompt_content" name="prompt_content" required>' . htmlspecialchars($prompt['cp_content'] ?? '') . '</textarea>
                    <p style="color: #6c757d; margin-top: 5px;">※ 변수는 {변수명} 형식으로 입력하세요.</p>
                </div>
            </div>

            <div class="form-section">
                <div class="variable-header">
                    <h2>입력 변수 설정</h2>
                    <button type="button" class="btn-add-variable" onclick="addVariable()">+ 변수 추가</button>
                </div>
                <div id="variables-container">';

    // 기존 변수들 출력
    if (!empty($variables)) {
        foreach ($variables as $index => $var) {
            echo '<div class="variable-item">
                    <input type="hidden" name="variables[' . $index . '][id]" value="' . $var['cv_idx'] . '">
                    <button type="button" class="btn-remove" onclick="removeVariable(this)">삭제</button>
                    <div class="form-group">
                        <label>변수명</label>
                        <input type="text" name="variables[' . $index . '][name]" value="' . htmlspecialchars($var['cv_name']) . '" required>
                    </div>
                    <div class="form-group">
                        <label>설명</label>
                        <input type="text" name="variables[' . $index . '][description]" value="' . htmlspecialchars($var['cv_description']) . '" required>
                    </div>
                    <div class="form-group">
                        <label>타입</label>
                        <select name="variables[' . $index . '][type]">
                            <option value="text" ' . ($var['cv_type'] == 'text' ? 'selected' : '') . '>텍스트</option>
                            <option value="number" ' . ($var['cv_type'] == 'number' ? 'selected' : '') . '>숫자</option>
                            <option value="date" ' . ($var['cv_type'] == 'date' ? 'selected' : '') . '>날짜</option>
                        </select>
                    </div>
                    <div class="required-toggle">
                        <input type="checkbox" name="variables[' . $index . '][required]" value="Y" ' . ($var['cv_required'] == 'Y' ? 'checked' : '') . '>
                        <label>필수 입력</label>
                    </div>
                </div>';
        }
    }

    echo '</div>
            </div>

            <div class="buttons-container">
                <button type="button" class="btn-back" onclick="location.href=\'admin_categories.php\'">돌아가기</button>
                <button type="submit" class="btn-save">저장하기</button>
            </div>
          </form>
        </div>

        <script>
        function addVariable() {
            const container = document.getElementById("variables-container");
            const index = container.children.length;
            
            const variableHtml = `
                <div class="variable-item">
                    <button type="button" class="btn-remove" onclick="removeVariable(this)">삭제</button>
                    <div class="form-group">
                        <label>변수명</label>
                        <input type="text" name="variables[${index}][name]" required>
                    </div>
                    <div class="form-group">
                        <label>설명</label>
                        <input type="text" name="variables[${index}][description]" required>
                    </div>
                    <div class="form-group">
                        <label>타입</label>
                        <select name="variables[${index}][type]">
                            <option value="text">텍스트</option>
                            <option value="number">숫자</option>
                            <option value="date">날짜</option>
                        </select>
                    </div>
                    <div class="required-toggle">
                        <input type="checkbox" name="variables[${index}][required]" value="Y">
                        <label>필수 입력</label>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML("beforeend", variableHtml);
        }

        function removeVariable(button) {
            button.parentElement.remove();
            reorderVariables();
        }

        function reorderVariables() {
            const container = document.getElementById("variables-container");
            const items = container.getElementsByClassName("variable-item");
            
            Array.from(items).forEach((item, index) => {
                const inputs = item.getElementsByTagName("input");
                const selects = item.getElementsByTagName("select");
                
                Array.from(inputs).forEach(input => {
                    const name = input.getAttribute("name");
                    if (name) {
                        input.setAttribute("name", name.replace(/variables\[\d+\]/, `variables[${index}]`));
                    }
                });
                
                Array.from(selects).forEach(select => {
                    const name = select.getAttribute("name");
                    if (name) {
                        select.setAttribute("name", name.replace(/variables\[\d+\]/, `variables[${index}]`));
                    }
                });
            });
        }
        </script>
    </body>
    </html>';

} catch (Exception $e) {
    echo '<div class="error-message">오류 발생: ' . htmlspecialchars($e->getMessage()) . '</div>';
} 