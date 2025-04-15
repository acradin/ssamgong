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

    // POST 요청 처리 (카테고리 상태 변경)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['delete_category'])) {
            $categoryId = (int)$_POST['delete_category'];
            $newStatus = 'N';
            $actionMessage = '비활성화';
        } elseif (isset($_POST['activate_category'])) {
            $categoryId = (int)$_POST['activate_category'];
            $newStatus = 'Y';
            $actionMessage = '활성화';
        }
        
        if (isset($categoryId) && isset($newStatus)) {
            try {
                // 상위 카테고리도 함께 활성화 (하위 카테고리 활성화 시에만)
                if ($newStatus === 'Y') {
                    $category = $db->get('category_t', '*', 'ct_idx = ?', [$categoryId]);
                    if (!empty($category) && !empty($category[0]['parent_idx'])) {
                        $db->update('category_t', 
                            ['ct_status' => $newStatus], 
                            'ct_idx = ?', 
                            [$category[0]['parent_idx']]
                        );
                    }
                }

                // 카테고리 상태 변경
                $db->update('category_t', 
                    ['ct_status' => $newStatus], 
                    'ct_idx = ?', 
                    [$categoryId]
                );

                // 하위 카테고리도 모두 변경
                $db->update('category_t', 
                    ['ct_status' => $newStatus], 
                    'parent_idx = ?', 
                    [$categoryId]
                );

                // 연관된 변수들도 모두 변경
                $db->update('chatbot_variable_t', 
                    ['cv_status' => $newStatus], 
                    'ct_idx = ?', 
                    [$categoryId]
                );

                // 프롬프트도 변경
                $db->update('chatbot_prompt_t', 
                    ['cp_status' => $newStatus], 
                    'ct_idx = ?', 
                    [$categoryId]
                );

                $success_message = "카테고리가 성공적으로 {$actionMessage}되었습니다.";

            } catch (Exception $e) {
                $error_message = "{$actionMessage} 중 오류가 발생했습니다: " . $e->getMessage();
            }
        }
    }

    // 메인 카테고리 조회 (모든 상태의 카테고리)
    $mainCategories = $db->get('category_t', '*', 'parent_idx IS NULL ORDER BY ct_order');
    
    // HTML 출력 시작
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>카테고리 관리</title>
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
            h1 { 
                color: #333;
                margin-bottom: 30px;
            }
            .category-section {
                margin-bottom: 40px;
            }
            .main-category {
                background-color: #f8f9fa;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 6px;
                border-left: 4px solid #007bff;
            }
            .main-category h2 {
                margin: 0;
                color: #007bff;
                font-size: 1.4em;
            }
            .sub-categories {
                margin-left: 30px;
                margin-top: 15px;
            }
            .sub-category {
                background-color: white;
                padding: 12px;
                margin-bottom: 10px;
                border: 1px solid #dee2e6;
                border-radius: 4px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .sub-category:hover {
                background-color: #f8f9fa;
            }
            .category-info {
                flex-grow: 1;
            }
            .category-name {
                font-weight: bold;
                color: #495057;
            }
            .category-order {
                color: #6c757d;
                font-size: 0.9em;
                margin-left: 10px;
            }
            .category-status {
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 0.8em;
            }
            .status-Y {
                background-color: #d4edda;
                color: #155724;
            }
            .status-N {
                background-color: #f8d7da;
                color: #721c24;
            }
            .add-button {
                background-color: #28a745;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                margin-bottom: 20px;
            }
            .add-button:hover {
                background-color: #218838;
            }
            .add-sub-button {
                background-color: #6c757d;
                color: white;
                padding: 6px 12px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 0.9em;
                margin-left: 10px;
            }
            .add-sub-button:hover {
                background-color: #5a6268;
            }
            .category-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .category-title {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .error-message {
                background-color: #f8d7da;
                color: #721c24;
                padding: 15px;
                border-radius: 4px;
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
            .btn-delete {
                background-color: #dc3545;
                color: white;
                padding: 6px 12px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 0.9em;
                margin-left: 10px;
            }
            .btn-delete:hover {
                background-color: #c82333;
            }
            .btn-deactivate {
                background-color: #6c757d;
                color: white;
                padding: 6px 12px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 0.9em;
                margin-left: 10px;
            }
            .btn-deactivate:hover {
                background-color: #5a6268;
            }
            .btn-activate {
                background-color: #28a745;
                color: white;
                padding: 6px 12px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 0.9em;
                margin-left: 10px;
            }
            .btn-activate:hover {
                background-color: #218838;
            }
            .modal {
                display: none;
                position: fixed;
                z-index: 1;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.4);
            }
            .modal-content {
                background-color: #fefefe;
                margin: 15% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
                max-width: 500px;
                border-radius: 8px;
            }
            .modal-buttons {
                margin-top: 20px;
                text-align: right;
            }
            .modal-buttons button {
                margin-left: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">';
    
    // 성공/에러 메시지 출력
    if ($error_message) {
        echo '<div class="error-message">' . htmlspecialchars($error_message) . '</div>';
    }
    if ($success_message) {
        echo '<div class="success-message" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;">' 
             . htmlspecialchars($success_message) . '</div>';
    }

    echo '<div class="admin-info">
            <span>관리자: ' . htmlspecialchars($admin[0]['mt_nickname']) . ' (ID: ' . htmlspecialchars($admin[0]['mt_id']) . ')</span>
            <span class="admin-level">관리자 레벨: ' . $admin[0]['mt_level'] . '</span>
          </div>
          <h1>카테고리 관리</h1>
          <button class="add-button" onclick="location.href=\'add_category.php\'">상위 카테고리 추가</button>';

    // 메인 카테고리 조회
    if (!empty($mainCategories)) {
        foreach ($mainCategories as $mainCat) {
            echo '<div class="category-section">';
            echo '<div class="main-category">';
            echo '<div class="category-header">';
            echo '<div class="category-title">';
            echo '<h2>' . htmlspecialchars($mainCat['ct_name']) . 
                 '<span class="category-order">(순서: ' . $mainCat['ct_order'] . ')</span>' .
                 '<span class="category-status status-' . $mainCat['ct_status'] . '">' . 
                 ($mainCat['ct_status'] === 'Y' ? '활성' : '비활성') . '</span></h2>';
            echo '</div>';
            echo '<div>';
            echo '<button class="add-sub-button" onclick="location.href=\'add_category.php?parent_id=' . $mainCat['ct_idx'] . '\'">하위 카테고리 추가</button>';
            if ($mainCat['ct_status'] === 'Y') {
                echo '<button class="btn-deactivate" onclick="confirmDeactivate(' . $mainCat['ct_idx'] . ', \'' . htmlspecialchars($mainCat['ct_name']) . '\')">비활성화</button>';
            } else {
                echo '<button class="btn-activate" onclick="confirmActivate(' . $mainCat['ct_idx'] . ', \'' . htmlspecialchars($mainCat['ct_name']) . '\')">활성화</button>';
            }
            echo '</div>';
            echo '</div>';
            
            // 하위 카테고리 조회 (모든 상태의 카테고리)
            $subCategories = $db->get('category_t', '*', 
                'parent_idx = ? ORDER BY ct_order', 
                [$mainCat['ct_idx']]
            );
            
            if (!empty($subCategories)) {
                echo '<div class="sub-categories">';
                foreach ($subCategories as $subCat) {
                    echo '<div class="sub-category">';
                    echo '<div class="category-info" onclick="location.href=\'edit_category.php?category_id=' . $subCat['ct_idx'] . '\'" style="cursor: pointer;">';
                    echo '<span class="category-name">' . htmlspecialchars($subCat['ct_name']) . '</span>';
                    echo '<span class="category-order">(순서: ' . $subCat['ct_order'] . ')</span>';
                    echo '<span class="edit-hint">[클릭하여 편집]</span>';
                    echo '</div>';
                    echo '<div>';
                    echo '<span class="category-status status-' . $subCat['ct_status'] . '">' . 
                         ($subCat['ct_status'] === 'Y' ? '활성' : '비활성') . '</span>';
                    if ($subCat['ct_status'] === 'Y') {
                        echo '<button class="btn-deactivate" onclick="confirmDeactivate(' . $subCat['ct_idx'] . ', \'' . htmlspecialchars($subCat['ct_name']) . '\')">비활성화</button>';
                    } else {
                        echo '<button class="btn-activate" onclick="confirmActivate(' . $subCat['ct_idx'] . ', \'' . htmlspecialchars($subCat['ct_name']) . '\')">활성화</button>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            }
            
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>등록된 카테고리가 없습니다.</p>';
    }

    // 비활성화 확인 모달
    echo '<div id="deleteModal" class="modal">
            <div class="modal-content">
                <h2>카테고리 비활성화</h2>
                <p><span id="categoryNameSpan"></span> 카테고리를 비활성화하시겠습니까?</p>
                <p style="color: #6c757d;">※ 주의: 하위 카테고리와 관련 데이터도 모두 비활성화됩니다.</p>
                <div class="modal-buttons">
                    <form method="post" style="display: inline;">
                        <input type="hidden" id="deleteCategoryId" name="delete_category" value="">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">취소</button>
                        <button type="submit" class="btn-deactivate">비활성화</button>
                    </form>
                </div>
            </div>
          </div>';

    // 활성화 확인 모달
    echo '<div id="activateModal" class="modal">
            <div class="modal-content">
                <h2>카테고리 활성화</h2>
                <p><span id="activateCategoryNameSpan"></span> 카테고리를 활성화하시겠습니까?</p>
                <p style="color: #28a745;">※ 하위 카테고리와 관련 데이터도 모두 활성화됩니다.</p>
                <div class="modal-buttons">
                    <form method="post" style="display: inline;">
                        <input type="hidden" id="activateCategoryId" name="activate_category" value="">
                        <button type="button" class="btn btn-secondary" onclick="closeActivateModal()">취소</button>
                        <button type="submit" class="btn-activate">활성화</button>
                    </form>
                </div>
            </div>
          </div>
          <script>
          function confirmDeactivate(categoryId, categoryName) {
              document.getElementById("deleteModal").style.display = "block";
              document.getElementById("deleteCategoryId").value = categoryId;
              document.getElementById("categoryNameSpan").textContent = categoryName;
          }

          function confirmActivate(categoryId, categoryName) {
              document.getElementById("activateModal").style.display = "block";
              document.getElementById("activateCategoryId").value = categoryId;
              document.getElementById("activateCategoryNameSpan").textContent = categoryName;
          }

          function closeModal() {
              document.getElementById("deleteModal").style.display = "none";
          }

          function closeActivateModal() {
              document.getElementById("activateModal").style.display = "none";
          }

          // 모달 외부 클릭 시 닫기
          window.onclick = function(event) {
              if (event.target == document.getElementById("deleteModal")) {
                  closeModal();
              }
              if (event.target == document.getElementById("activateModal")) {
                  closeActivateModal();
              }
          }
          </script>';

    echo '</div></body></html>';

} catch (Exception $e) {
    echo '<div class="error-message">오류 발생: ' . htmlspecialchars($e->getMessage()) . '</div>';
} 