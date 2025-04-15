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

    // 페이지네이션 설정
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // 오늘 날짜와 시간
    $today = date('Y-m-d');
    
    // 카테고리 목록 조회
    $mainCategories = $db->get('category_t', '*', 'parent_idx IS NULL AND ct_status = "Y"', [], 'ct_order ASC');
    $subCategories = [];
    if (!empty($mainCategories)) {
        $subCategories = $db->get('category_t', '*', 'parent_idx IS NOT NULL AND ct_status = "Y"', [], 'parent_idx ASC, ct_order ASC');
    }

    // 검색 조건
    $where = '';
    $params = [];
    
    // 카테고리 필터링
    if (isset($_GET['main_category']) && !empty($_GET['main_category'])) {
        $where .= ($where ? ' AND ' : '') . 'child.parent_idx = ?';
        $params[] = $_GET['main_category'];
        
        if (isset($_GET['sub_category']) && !empty($_GET['sub_category'])) {
            $where .= ' AND cs.ct_idx = ?';
            $params[] = $_GET['sub_category'];
        }
    }
    
    if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
        $where .= ($where ? ' AND ' : '') . 'm.mt_id LIKE ?';
        $params[] = '%' . $_GET['user_id'] . '%';
    }

    // 시작일이 설정되어 있지 않으면 전체 데이터 조회
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $start_date = min($_GET['start_date'], $today);
        $where .= ($where ? ' AND ' : '') . 'cs.created_at >= ?';
        $params[] = $start_date . ' 00:00:00';
    }

    // 종료일의 기본값은 오늘
    $end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? 
                min($_GET['end_date'], $today) : 
                $today;
    $where .= ($where ? ' AND ' : '') . 'cs.created_at <= ?';
    $params[] = ($end_date == $today) ? date('Y-m-d H:i:s') : $end_date . ' 23:59:59';

    // 전체 세션 수 조회
    $totalQuery = "SELECT COUNT(DISTINCT cs.cs_idx) as total 
                  FROM chat_sessions cs
                  LEFT JOIN member_t m ON cs.mt_idx = m.mt_idx
                  LEFT JOIN category_t child ON cs.ct_idx = child.ct_idx
                  LEFT JOIN category_t parent ON child.parent_idx = parent.ct_idx";
    if ($where) {
        $totalQuery .= " WHERE " . $where;
    }
    $total = $db->rawQuery($totalQuery, $params)[0]['total'];
    $totalPages = ceil($total / $limit);

    // 채팅 세션 조회
    $query = "SELECT cs.*, m.mt_id, m.mt_nickname, m.mt_name, m.mt_hp, m.mt_email,
              parent.ct_name as main_category_name,
              child.ct_name as sub_category_name,
              GROUP_CONCAT(
                  CONCAT(
                      IF(cm.is_bot, 'AI: ', '사용자: '),
                      cm.content,
                      '|',
                      cm.created_at
                  ) ORDER BY cm.created_at ASC SEPARATOR '||'
              ) as messages
              FROM chat_sessions cs
              LEFT JOIN member_t m ON cs.mt_idx = m.mt_idx
              LEFT JOIN category_t child ON cs.ct_idx = child.ct_idx
              LEFT JOIN category_t parent ON child.parent_idx = parent.ct_idx
              LEFT JOIN chat_messages cm ON cs.cs_idx = cm.cs_idx";
    if ($where) {
        $query .= " WHERE " . $where;
    }
    $query .= " GROUP BY cs.cs_idx ORDER BY cs.created_at DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    
    $histories = $db->rawQuery($query, $params);

    // HTML 출력 시작
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>챗봇 히스토리</title>
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
            .search-form {
                background-color: #f8f9fa;
                padding: 20px;
                border-radius: 6px;
                margin-bottom: 30px;
            }
            .search-form .form-group {
                margin-bottom: 15px;
            }
            .search-form label {
                display: inline-block;
                width: 120px;
                font-weight: bold;
            }
            .search-form input[type="text"],
            .search-form input[type="date"],
            .search-form select {
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                width: 200px;
            }
            .search-form button {
                background-color: #007bff;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            .search-form button:hover {
                background-color: #0056b3;
            }
            .history-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            .history-table th,
            .history-table td {
                padding: 12px;
                border: 1px solid #ddd;
                text-align: left;
            }
            .history-table th {
                background-color: #f8f9fa;
                font-weight: bold;
            }
            .history-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .history-table tr:hover {
                background-color: #f5f5f5;
            }
            .pagination {
                margin-top: 20px;
                text-align: center;
            }
            .pagination a {
                display: inline-block;
                padding: 8px 16px;
                text-decoration: none;
                color: #007bff;
                border: 1px solid #ddd;
                margin: 0 4px;
            }
            .pagination a:hover {
                background-color: #f5f5f5;
            }
            .pagination .active {
                background-color: #007bff;
                color: white;
                border-color: #007bff;
            }
            .chat-bubble {
                max-width: 300px;
                padding: 10px;
                border-radius: 10px;
                margin: 5px 0;
            }
            .user-input {
                background-color: #e3f2fd;
                margin-right: auto;
            }
            .ai-output {
                background-color: #f5f5f5;
                margin-left: auto;
            }
            .no-results {
                text-align: center;
                padding: 20px;
                color: #666;
            }
            .chat-messages {
                max-height: 300px;
                overflow-y: auto;
                border: 1px solid #ddd;
                padding: 10px;
                border-radius: 4px;
            }
            .message-time {
                font-size: 0.8em;
                color: #666;
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
        echo '<div class="success-message">' . htmlspecialchars($success_message) . '</div>';
    }

    echo '<div class="admin-info">
            <span>관리자: ' . htmlspecialchars($admin[0]['mt_nickname']) . ' (ID: ' . htmlspecialchars($admin[0]['mt_id']) . ')</span>
            <span class="admin-level">관리자 레벨: ' . $admin[0]['mt_level'] . '</span>
          </div>
          <h1>챗봇 히스토리</h1>
          
          <form class="search-form" method="get">
            <div class="form-group">
                <label>상위 카테고리:</label>
                <select name="main_category" id="main_category" onchange="updateSubCategories()">
                    <option value="">전체</option>';
    foreach ($mainCategories as $cat) {
        $selected = (isset($_GET['main_category']) && $_GET['main_category'] == $cat['ct_idx']) ? 'selected' : '';
        echo '<option value="' . $cat['ct_idx'] . '" ' . $selected . '>' . htmlspecialchars($cat['ct_name']) . '</option>';
    }
    echo '</select>
            </div>
            <div class="form-group">
                <label>하위 카테고리:</label>
                <select name="sub_category" id="sub_category">
                    <option value="">전체</option>';
    foreach ($subCategories as $cat) {
        if ((!isset($_GET['main_category']) || $_GET['main_category'] == $cat['parent_idx'])) {
            $selected = (isset($_GET['sub_category']) && $_GET['sub_category'] == $cat['ct_idx']) ? 'selected' : '';
            echo '<option value="' . $cat['ct_idx'] . '" data-parent="' . $cat['parent_idx'] . '" ' . $selected . '>' 
                . htmlspecialchars($cat['ct_name']) . '</option>';
        }
    }
    echo '</select>
            </div>
            <div class="form-group">
                <label>사용자 ID:</label>
                <input type="text" name="user_id" value="' . (isset($_GET['user_id']) ? htmlspecialchars($_GET['user_id']) : '') . '">
            </div>
            <div class="form-group">
                <label>시작일:</label>
                <input type="date" name="start_date" max="' . $today . '" value="' . (isset($_GET['start_date']) ? htmlspecialchars(min($_GET['start_date'], $today)) : '') . '">
            </div>
            <div class="form-group">
                <label>종료일:</label>
                <input type="date" name="end_date" max="' . $today . '" value="' . $today . '">
            </div>
            <div class="form-group">
                <button type="submit">검색</button>
                <button type="button" onclick="location.href=\'chatbot_history.php\'">초기화</button>
            </div>
          </form>';

    if (!empty($histories)) {
        echo '<table class="history-table">
                <thead>
                    <tr>
                        <th>세션 ID</th>
                        <th>사용자 정보</th>
                        <th>상위 카테고리</th>
                        <th>하위 카테고리</th>
                        <th>대화 내용</th>
                        <th>시작 시간</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($histories as $history) {
            // 사용자 정보 구성
            $userInfo = [];
            if (!empty($history['mt_nickname'])) $userInfo[] = "닉네임: " . htmlspecialchars($history['mt_nickname']);
            if (!empty($history['mt_name'])) $userInfo[] = "이름: " . htmlspecialchars($history['mt_name']);
            if (!empty($history['mt_id'])) $userInfo[] = "ID: " . htmlspecialchars($history['mt_id']);
            if (!empty($history['mt_hp'])) $userInfo[] = "연락처: " . htmlspecialchars($history['mt_hp']);
            if (!empty($history['mt_email'])) $userInfo[] = "이메일: " . htmlspecialchars($history['mt_email']);
            
            echo '<tr>
                    <td>' . htmlspecialchars($history['session_id']) . '</td>
                    <td>' . implode("<br>", $userInfo) . '</td>
                    <td>' . htmlspecialchars($history['main_category_name']) . '</td>
                    <td>' . htmlspecialchars($history['sub_category_name']) . '</td>
                    <td class="chat-messages">';
            
            if (!empty($history['messages'])) {
                $messages = explode('||', $history['messages']);
                foreach ($messages as $message) {
                    list($content, $time) = explode('|', $message);
                    $isBot = strpos($content, 'AI: ') === 0;
                    echo '<div class="chat-bubble ' . ($isBot ? 'ai-output' : 'user-input') . '">' 
                         . htmlspecialchars($content) 
                         . '<span class="message-time">' . date('Y-m-d H:i:s', strtotime($time)) . '</span>'
                         . '</div>';
                }
            }
            
            echo '</td>
                    <td>' . $history['created_at'] . '</td>
                  </tr>';
        }
        
        echo '</tbody></table>';

        // 페이지네이션
        if ($totalPages > 1) {
            echo '<div class="pagination">';
            
            // 이전 페이지
            if ($page > 1) {
                echo '<a href="?page=' . ($page - 1) . '&' . http_build_query(array_filter($_GET, function($key) { return $key !== 'page'; }, ARRAY_FILTER_USE_KEY)) . '">&laquo;</a>';
            }
            
            // 페이지 번호
            for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++) {
                $active = $i == $page ? 'active' : '';
                echo '<a class="' . $active . '" href="?page=' . $i . '&' . http_build_query(array_filter($_GET, function($key) { return $key !== 'page'; }, ARRAY_FILTER_USE_KEY)) . '">' . $i . '</a>';
            }
            
            // 다음 페이지
            if ($page < $totalPages) {
                echo '<a href="?page=' . ($page + 1) . '&' . http_build_query(array_filter($_GET, function($key) { return $key !== 'page'; }, ARRAY_FILTER_USE_KEY)) . '">&raquo;</a>';
            }
            
            echo '</div>';
        }
    } else {
        echo '<div class="no-results">검색 결과가 없습니다.</div>';
    }

    // JavaScript 추가
    echo '<script>
    function updateSubCategories() {
        const mainCategory = document.getElementById("main_category").value;
        const subCategory = document.getElementById("sub_category");
        const options = subCategory.getElementsByTagName("option");
        
        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            if (option.value === "") {
                option.style.display = "block";  // "전체" 옵션은 항상 표시
                continue;
            }
            
            if (!mainCategory || option.getAttribute("data-parent") === mainCategory) {
                option.style.display = "block";
            } else {
                option.style.display = "none";
            }
        }
        
        // 선택된 하위 카테고리가 숨겨진 경우 "전체"로 초기화
        if (subCategory.selectedOptions[0].style.display === "none") {
            subCategory.value = "";
        }
    }
    
    // 페이지 로드 시 실행
    document.addEventListener("DOMContentLoaded", function() {
        updateSubCategories();
    });
    </script>';

    echo '</div></body></html>';

} catch (Exception $e) {
    echo '<div class="error-message">오류 발생: ' . htmlspecialchars($e->getMessage()) . '</div>';
} 