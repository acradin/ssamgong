<?php
require_once __DIR__ . '/../config/database.php';

// 세션 시작
session_start();
$_SESSION['_mt_idx'] = 1;  // 테스트용 사용자 ID

// 데이터베이스 연결
$db = Database::getInstance()->getConnection();

// URL에서 메인 카테고리 ID 가져오기
$mainCategoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;

// 테스트 결과를 저장할 배열
$tests = [];

// 헤더 출력
echo '<html><head><title>챗봇 페이지 테스트</title>';
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-result { margin: 10px 0; padding: 10px; border-radius: 5px; }
    .success { background: #d4edda; color: #155724; }
    .warning { background: #fff3cd; color: #856404; }
    .error { background: #f8d7da; color: #721c24; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    .category-list { margin: 20px 0; }
    .category-item { padding: 10px; margin: 5px 0; background: #f8f9fa; border-radius: 5px; }
    .variable-list { margin-left: 20px; }
    .variable-item { margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input[type="text"], select, input[type="date"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    .required { color: red; }
    button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
    button:hover { background: #0056b3; }
    .category-selector { margin-bottom: 20px; }
    .category-selector a { 
        display: inline-block; 
        padding: 10px 20px; 
        margin-right: 10px;
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
    }
    .category-selector a.active {
        background: #007bff;
        color: white;
        border-color: #0056b3;
    }
</style></head><body>';
echo '<h1>챗봇 페이지 테스트</h1>';

try {
    // 1. 회원 정보 및 포인트 확인
    $member = $db->get('member_t', '*', 'mt_idx = ?', [$_SESSION['_mt_idx']]);
    if (empty($member)) {
        throw new Exception('회원 정보를 찾을 수 없습니다.');
    }
    $member = $member[0];
    
    $tests[] = [
        'name' => '회원 정보 조회',
        'status' => 'success',
        'message' => "회원: {$member['mt_nickname']}, 포인트: {$member['mt_point']}"
    ];

    // 2. 챗봇 첫 사용 확인
    $usage = $db->get('chatbot_usage', '*', 'mt_idx = ?', [$_SESSION['_mt_idx']]);
    $isFirstUse = empty($usage);
    $isInFreePeriod = false;
    $remainingDays = 0;

    if ($isFirstUse) {
        $isInFreePeriod = true;
        $remainingDays = 7;
    } else {
        $firstUseDate = new DateTime($usage[0]['first_use_date']);
        $now = new DateTime();
        $diff = $now->diff($firstUseDate);
        $isInFreePeriod = $diff->days < 7;
        $remainingDays = max(0, 7 - $diff->days);
    }

    // 포인트 관련 정보
    $pointCost = 100;
    $canUsePoint = $isInFreePeriod || $member['mt_point'] >= $pointCost;
    $pointMessage = $isInFreePeriod 
        ? "무료 사용 기간입니다. (남은 기간: {$remainingDays}일)" 
        : "필요 포인트: {$pointCost} (보유 포인트: {$member['mt_point']})";

    $tests[] = [
        'name' => '챗봇 사용 기록',
        'status' => $isInFreePeriod ? 'success' : 'warning',
        'message' => $pointMessage
    ];

    // 3. 메인 카테고리 목록 조회
    $mainCategories = $db->get('category_t', '*', 'parent_idx IS NULL AND ct_status = "Y"');
    
    // 메인 카테고리 선택기 표시
    echo '<div class="category-selector">';
    foreach ($mainCategories as $cat) {
        $isActive = $mainCategoryId == $cat['ct_idx'] ? ' class="active"' : '';
        echo '<a href="?category=' . $cat['ct_idx'] . '"' . $isActive . '>' . 
             htmlspecialchars($cat['ct_name']) . '</a>';
    }
    echo '</div>';

    // 선택된 메인 카테고리가 있는 경우에만 하위 카테고리 표시
    if ($mainCategoryId !== null) {
        $mainCategory = null;
        foreach ($mainCategories as $cat) {
            if ($cat['ct_idx'] == $mainCategoryId) {
                $mainCategory = $cat;
                break;
            }
        }

        if ($mainCategory) {
            echo '<div class="category-list">';
            echo '<h2>' . htmlspecialchars($mainCategory['ct_name']) . ' 입력 변수</h2>';
            
            // 하위 카테고리 조회
            $subCategories = $db->get('category_t', '*', 
                'parent_idx = ? AND ct_status = "Y" ORDER BY ct_order', 
                [$mainCategoryId]
            );
            
            foreach ($subCategories as $subCat) {
                echo '<div class="category-item">';
                echo '<h3>' . htmlspecialchars($subCat['ct_name']) . '</h3>';
                
                // 변수 목록 조회
                $variables = $db->get('chatbot_variable_t', '*',
                    'ct_idx = ? AND cv_status = "Y" ORDER BY cv_order',
                    [$subCat['ct_idx']]
                );
                
                if (!empty($variables)) {
                    echo '<form method="post" action="generate_chatbot.php" class="variable-list" enctype="multipart/form-data">';
                    echo '<input type="hidden" name="category_id" value="' . $subCat['ct_idx'] . '">';
                    echo '<input type="hidden" name="is_free_period" value="' . ($isInFreePeriod ? '1' : '0') . '">';
                    
                    foreach ($variables as $var) {
                        echo '<div class="variable-item">';
                        echo '<div class="form-group">';
                        echo '<label>' . htmlspecialchars($var['cv_name']);
                        if ($var['cv_required'] === 'Y') {
                            echo ' <span class="required">*</span>';
                        }
                        echo '</label>';
                        echo '<p class="description">' . htmlspecialchars($var['cv_description']) . '</p>';
                        
                        if ($var['cv_type'] === 'select' && !empty($var['cv_options'])) {
                            $options = json_decode($var['cv_options'], true);
                            echo '<select name="var_' . $var['cv_idx'] . '"' . 
                                 ($var['cv_required'] === 'Y' ? ' required' : '') . '>';
                            echo '<option value="">선택하세요</option>';
                            foreach ($options as $option) {
                                echo '<option value="' . htmlspecialchars($option) . '">' . 
                                     htmlspecialchars($option) . '</option>';
                            }
                            echo '</select>';
                        } elseif ($var['cv_type'] === 'date') {
                            echo '<input type="date" name="var_' . $var['cv_idx'] . '"' . 
                                 ($var['cv_required'] === 'Y' ? ' required' : '') . '>';
                        } elseif ($var['cv_type'] === 'file') {
                            echo '<input type="file" name="var_' . $var['cv_idx'] . '"' . 
                                 ($var['cv_required'] === 'Y' ? ' required' : '') . 
                                 ' accept=".pdf">';
                        } else {
                            echo '<input type="text" name="var_' . $var['cv_idx'] . '"' . 
                                 ($var['cv_required'] === 'Y' ? ' required' : '') . 
                                 ' placeholder="입력하세요">';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                    
                    echo '<button type="submit">챗봇 생성하기</button>';
                    echo '</form>';
                } else {
                    echo '<p>설정된 변수가 없습니다.</p>';
                }
                
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div class="test-result warning">유효하지 않은 메인 카테고리입니다.</div>';
        }
    } else {
        echo '<div class="test-result warning">메인 카테고리를 선택해주세요.</div>';
    }

    // 결과 테이블 출력
    echo '<table>';
    echo '<tr><th>테스트 항목</th><th>상태</th><th>메시지</th></tr>';
    
    foreach ($tests as $test) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($test['name']) . '</td>';
        echo '<td><div class="test-result ' . $test['status'] . '">' . 
             ucfirst($test['status']) . '</div></td>';
        echo '<td>' . htmlspecialchars($test['message']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';

} catch (Exception $e) {
    echo '<div class="test-result error">오류 발생: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

echo '</body></html>'; 