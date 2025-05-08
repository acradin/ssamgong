<?php
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head.inc.php";
$chk_menu = '13';
$chk_sub_menu = '2';
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head_menu.inc.php";

try {
    // 페이지네이션 설정
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // 검색 조건
    $whereClause = [];
    $params = [];
    
    // 정렬 조건
    $orderBy = "cs.created_at DESC";  // 기본 정렬은 최신순

    // 날짜 필터
    if (isset($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $whereClause[] = 'cs.created_at >= ? AND cs.created_at < DATE_ADD(?, INTERVAL 1 DAY)';
        $params[] = $_GET['start_date'] . ' 00:00:00';
        $params[] = $_GET['end_date'];
    }

    // 검색 조건 부분에 카테고리 필터 추가
    if (isset($_GET['chatbot']) && !empty($_GET['chatbot'])) {
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            // 카테고리가 선택된 경우
            $whereClause[] = 'cs.ct_idx = ?';
            $params[] = $_GET['category'];
        } else {
            // 챗봇만 선택된 경우
            $whereClause[] = 'ct.parent_idx = ?';
            $params[] = $_GET['chatbot'];
        }
    }

    // 기본 쿼리 작성
    $query = "SELECT 
        cs.cs_idx,
        cs.session_id,
        cs.created_at,
        m.mt_id,
        m.mt_nickname,
        pct.ct_name as bot_name,
        ct.ct_name as category_name
    FROM chat_sessions cs
    LEFT JOIN member_t m ON cs.mt_idx = m.mt_idx
    LEFT JOIN category_t ct ON cs.ct_idx = ct.ct_idx
    LEFT JOIN category_t pct ON ct.parent_idx = pct.ct_idx";

    // WHERE 절 추가
    if (!empty($whereClause)) {
        $query .= " WHERE " . implode(' AND ', $whereClause);
    }

    // 정렬과 제한 추가
    $query .= " ORDER BY " . $orderBy;
    
    if (empty($params)) {
        // 파라미터가 없는 경우
        $query .= " LIMIT $offset, $limit";
        $histories = $DB->rawQuery($query);
        
        // 전체 개수 조회
        $total_result = $DB->rawQuery("SELECT COUNT(*) as total FROM chat_sessions cs 
            LEFT JOIN member_t m ON cs.mt_idx = m.mt_idx
            LEFT JOIN category_t ct ON cs.ct_idx = ct.ct_idx
            LEFT JOIN category_t pct ON ct.parent_idx = pct.ct_idx");
    } else {
        // 파라미터가 있는 경우
        $query .= " LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
        $histories = $DB->rawQuery($query, $params);
        
        // 전체 개수 조회를 위한 쿼리
        $count_query = "SELECT COUNT(*) as total FROM chat_sessions cs 
            LEFT JOIN member_t m ON cs.mt_idx = m.mt_idx
            LEFT JOIN category_t ct ON cs.ct_idx = ct.ct_idx
            LEFT JOIN category_t pct ON ct.parent_idx = pct.ct_idx";
        if (!empty($whereClause)) {
            $count_query .= " AND " . implode(' AND ', $whereClause);
        }
        $total_result = $DB->rawQuery($count_query, array_slice($params, 0, -2));
    }

    $total_records = $total_result[0]['total'];
    $total_pages = ceil($total_records / $limit);

} catch (Exception $e) {
    $error_message = $e->getMessage();
    $histories = [];
    $total_pages = 0;
}

// 페이지 그룹 계산
$page_group = ceil($page / 10);
$start_page = ($page_group - 1) * 10 + 1;
$end_page = min($start_page + 9, $total_pages);
?>

        <style>
/* 비활성화된 select 박스의 스타일 */
select:disabled {
    background-color: #e9ecef !important;  /* 회색 배경 */
    cursor: not-allowed !important;        /* 커서 모양 변경 */
    opacity: 0.7 !important;               /* 약간 투명하게 */
    color: #6c757d !important;            /* 텍스트 색상 회색으로 */
    border-color: #ced4da !important;      /* 테두리 색상 */
}

/* 비활성화된 select 박스의 옵션 스타일 */
select:disabled option {
    color: #6c757d !important;
    background-color: #e9ecef !important;
}

/* 선택된 행 스타일 추가 */
.chat-row.selected {
    background-color: #e3f2fd !important;  /* 연한 파란색 배경 */
    border-left: 4px solid #2196f3 !important;  /* 왼쪽 파란색 보더 */
    font-weight: 500;  /* 약간 더 진한 글씨 */
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);  /* 살짝 들떠보이는 효과 */
}

/* 호버 효과도 수정 */
.table-hover tbody tr:hover {
    background-color: #f5f9ff !important;  /* 호버시 연한 파란색 */
    cursor: pointer;  /* 클릭 가능함을 나타내는 커서 */
}

/* 채팅 메시지 스타일 */
.chat-messages tr {
    border: none !important;
}

.chat-messages td {
    border: none !important;
    padding: 8px 15px;
}

.message-bubble {
    max-width: 70%; /* 최대 너비 70%로 제한 */
    padding: 12px 15px;
    border-radius: 12px;
    position: relative;
    margin: 8px 0;
    border: 1px solid #e0e0e0;
    word-wrap: break-word;
}

.user-bubble {
    margin-left: auto; /* 오른쪽 정렬 유지 */
    margin-right: 15px; /* 오른쪽 여백 */
    background-color: #e3f2fd;
    border-color: #90caf9;
}

.bot-bubble {
    margin-right: auto; /* 왼쪽 정렬 유지 */
    margin-left: 15px; /* 왼쪽 여백 */
    background-color: #f5f5f5;
    border-color: #e0e0e0;
}

.message-time {
    display: block;
    text-align: right;
    font-size: 0.8em;
    color: #757575;
    margin-top: 5px;
}

/* 테이블 컨테이너 스타일 수정 */
.table-container {
    position: relative;
}

/* 세션 테이블 컨테이너 스타일 */
#log-container {
    min-height: 400px;
    height: auto;
    width: calc(60% - 1rem); /* 전체 공간의 60%에서 gap의 절반을 뺌 */
}

/* 채팅 테이블 컨테이너 스타일 */
#chatbot-container {
    overflow: hidden;
    width: calc(40% - 1rem); /* 전체 공간의 40%에서 gap의 절반을 뺌 */
}

/* 채팅 컨테이너 스타일 */
.chat-container {
    overflow-y: auto;
    padding: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* 테이블 래퍼 스타일 */
.tables-wrapper {
    display: flex;
    gap: 2rem; /* 32px */
    align-items: flex-start;
    margin-bottom: 2rem;
    width: 100%;
}

/* 테이블 헤더 고정 */
.table-container thead th {
    background-color: #343a40;
    color: white;
}
</style>

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">결과 조회</h4>
                    <form method="post" name="frm_list" id="frm_list" onsubmit="return false;">
                        <input type="hidden" name="act" id="act" value="list" />
                        <input type="hidden" name="obj_pg" id="obj_pg" value="1" />
                        <input type="hidden" name="obj_orderby" id="obj_orderby" value="" />
                        <input type="hidden" name="obj_order_desc_asc" id="obj_order_desc_asc" value="1" />

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="form-group row align-items-center mb-0">
                                    <label class="col-sm-1 col-form-label">카테고리</label>
                                    <div class="col-sm-4">
                                        <select class="form-control form-select" id="chatbot" onchange="loadCategories(this.value)">
                                            <option value="">챗봇 선택</option>
                                            <?php
                                            // 챗봇 가져오기 (활성화된 것만)
                                            $parent_categories = $DB->rawQuery("
                                                SELECT ct_idx, ct_name 
                                                FROM category_t 
                                                WHERE parent_idx IS NULL 
                                                AND ct_status = 'Y' 
                                                ORDER BY ct_order"
                                            );
                                            foreach ($parent_categories as $category) {
                                                $selected = (isset($_GET['chatbot']) && $_GET['chatbot'] == $category['ct_idx']) ? 'selected' : '';
                                                echo "<option value='{$category['ct_idx']}' {$selected}>{$category['ct_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="select-wrapper">
                                            <select class="form-control form-select" id="category" disabled>
                                                <option value="">카테고리 선택</option>
                                            </select>
                        </div>
                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="form-group row align-items-center mb-0">
                                    <label class="col-sm-1 col-form-label">기간 검색</label>
                                    <div class="col-sm-4">
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm" id="date_range" name="date_range" 
                                                   value="<?= (isset($_GET['start_date']) && isset($_GET['end_date'])) ? $_GET['start_date'] . ' to ' . $_GET['end_date'] : '' ?>" 
                                                   placeholder="기간별 검색" readonly onclick="clearDateIfExists(event)">
                            </div>
                        </div>
                    </div>
                            </li>
                            <li class="list-group-item">
                                <div class="form-group row align-items-center mb-0">
                                    <div class="col-sm-12 text-center">
                                        <input type="button" class="btn btn-info" value="검색" onclick="f_apply_filters()">
                                        <input type="button" class="btn btn-secondary" value="초기화" onclick="f_reset_filter()">
                            </div>
                        </div>
                            </li>
                        </ul>
                    </form>

                    <!-- 테이블 영역 -->
                    <div class="tables-wrapper mt-4">
                        <!-- 세션 목록 테이블 (왼쪽) - 60% -->
                        <div id="log-container" class="table-container" style="width: 60%;">
                            <table class="table table-bordered table-hover inx-table inx-table-card">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center align-middle" style="width: 15%">세션 ID</th>
                                        <th class="text-center align-middle" style="width: 25%">사용자 정보</th>
                                        <th class="text-center align-middle" style="width: 20%">챗봇 이름</th>
                                        <th class="text-center align-middle" style="width: 25%">카테고리</th>
                                        <th class="text-center align-middle" style="width: 15%">일시</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($histories)): ?>
                                    <?php foreach ($histories as $history): ?>
                                            <tr class="chat-row text-center" data-cs-idx="<?= $history['cs_idx'] ?>">
                                            <td><?= htmlspecialchars($history['session_id']) ?></td>
                                                <td class="text-left">
                                                    <p class="mb-0">닉네임: <?= htmlspecialchars($history['mt_nickname']) ?></p>
                                                    <small class="text-muted">ID: <?= htmlspecialchars($history['mt_id']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($history['bot_name']) ?></td>
                                            <td><?= htmlspecialchars($history['category_name']) ?></td>
                                                <td><?= date('Y-m-d H:i', strtotime($history['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <p class="text-muted mb-0">
                                                        <i class="mdi mdi-information-outline mr-1"></i>
                                                        세션이 없습니다
                                                    </p>
                                                </div>
                                            </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                        
                        <!-- 채팅 내역 테이블 (오른쪽) - 40% -->
                        <div id="chatbot-container" class="table-container" style="width: 40%;">
                            <table class="table table-bordered inx-table inx-table-card h-100">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center align-middle">대화내역</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="height: 100%; padding: 0;">
                                            <div class="chat-container d-flex align-items-center justify-content-center">
                                                <!-- 초기 메시지 -->
                                                <p class="text-muted text-center">
                                                    <i class="mdi mdi-information-outline mr-1"></i>
                                                    세션을 선택해주세요
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                    <!-- 페이지네이션 -->
                    <?php if ($total_pages > 1): ?>
                        <div class="mt-4">
                            <nav>
                                <ul class="pagination justify-content-center">
                <?php
                                    $url_params = $_GET;
                                    unset($url_params['page']);
                                    $query_string = http_build_query($url_params);
                                    $query_string = $query_string ? '&' . $query_string : '';
                                    
                                    // 현재 페이지 그룹 계산
                                    $pageGroup = ceil($page/10);
                                    $startPage = ($pageGroup - 1) * 10 + 1;
                                    $endPage = min($startPage + 9, $total_pages);
                                    $prevGroup = $startPage - 10 > 0 ? $startPage - 10 : 1;
                                    $nextGroup = $endPage + 1 <= $total_pages ? $endPage + 1 : $total_pages;
                                    ?>
                                    
                            <!-- 첫 페이지로 -->
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=1<?= $query_string ?>" title="첫 페이지">
                                                &lt;&lt;
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- 이전 10개 페이지로 -->
                                    <?php if ($startPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $prevGroup . $query_string ?>" title="이전 10페이지">
                                &lt;
                            </a>
                                        </li>
                        <?php endif; ?>

                                    <!-- 페이지 번호 -->
                                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i . $query_string ?>"><?= $i ?></a>
                                        </li>
                        <?php endfor; ?>

                                    <!-- 다음 10개 페이지로 -->
                                    <?php if ($endPage < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $nextGroup . $query_string ?>" title="다음 10페이지">
                                &gt;
                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                            <!-- 마지막 페이지로 -->
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $total_pages . $query_string ?>" title="마지막 페이지">
                                                &gt;&gt;
                                            </a>
                                        </li>
                        <?php endif; ?>
                                </ul>
                            </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
            </div>
        </div>

        <script>
// 기존 JavaScript 코드 유지하되 flatpickr 설정만 수정
$(document).ready(function() {
    const datePicker = flatpickr("#date_range", {
        mode: "range",
                    locale: "ko",
                    dateFormat: "Y-m-d",
        rangeSeparator: " to ",
        placeholder: "기간별 검색",
        static: true,
                    monthSelectorType: "static",
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                f_apply_filters();
            }
        }
    });
});

// 필터 관련 함수 수정
function loadCategories(parentId) {
    const categorySelect = document.getElementById('category');
    
    if (!parentId) {
        categorySelect.disabled = true;
        categorySelect.innerHTML = '<option value="">카테고리 선택</option>';
        categorySelect.style.backgroundColor = '#e9ecef';
        return;
    }

    // AJAX로 활성화된 카테고리만 가져오기
    fetch(`get_categories.php?parent_idx=${parentId}&status=Y`)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">카테고리 선택</option>';
            data.forEach(category => {
                const selected = (new URLSearchParams(window.location.search).get('category') == category.ct_idx) ? 'selected' : '';
                options += `<option value="${category.ct_idx}" ${selected}>${category.ct_name}</option>`;
            });
            categorySelect.innerHTML = options;
            categorySelect.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            categorySelect.disabled = true;
            categorySelect.innerHTML = '<option value="">카테고리 선택</option>';
        });
}

function f_apply_filters() {
    const params = new URLSearchParams();
    
    // 페이지 초기화
    params.append('page', '1');
    
    // 카테고리 필터
    const chatbot = document.getElementById('chatbot').value;
    const category = document.getElementById('category').value;
    
    if (chatbot) {
        params.append('chatbot', chatbot);
        if (category) {
            params.append('category', category);
        }
    }
    
    // 날짜 필터
    const dateRange = document.getElementById('date_range').value;
    if (dateRange) {
        const [startDate, endDate] = dateRange.split(' to ');
        if (startDate && endDate) {
            params.append('start_date', startDate);
            params.append('end_date', endDate);
        }
    }
    
    // URL 이동
    window.location.href = `?${params.toString()}`;
}

function f_reset_filter() {
    document.getElementById('chatbot').value = '';
    document.getElementById('category').value = '';
    document.getElementById('category').disabled = true;
    document.getElementById('date_range').value = '';
    window.location.href = '?';
}

// 페이지 로드 시 초기 상태 설정
document.addEventListener('DOMContentLoaded', function() {
    const chatbot = document.getElementById('chatbot').value;
    const category = document.getElementById('category');
    
    if (!chatbot) {
        category.disabled = true;
        category.innerHTML = '<option value="">카테고리 선택</option>';
    } else {
        loadCategories(chatbot);
    }
});

// 채팅 내역 관련 JavaScript 수정
            document.addEventListener('DOMContentLoaded', function() {
                const rows = document.querySelectorAll('.chat-row');
                const chatbotContainer = document.getElementById('chatbot-container');

                // 초기 메시지 템플릿
                const initialMessage = `
                    <table class="table table-bordered inx-table inx-table-card h-100">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center align-middle">대화내역</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="height: 100%; padding: 0;">
                                    <div class="chat-container d-flex align-items-center justify-content-center">
                                        <p class="text-muted text-center">
                                            <i class="mdi mdi-information-outline mr-1"></i>
                                            세션을 선택해주세요
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                `;

                const errorMessage = (message) => `
                    <table class="table table-bordered inx-table inx-table-card h-100">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center align-middle">대화내역</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">${message}</td>
                            </tr>
                        </tbody>
                    </table>
                `;

                // 초기 상태 설정
                chatbotContainer.innerHTML = initialMessage;

                rows.forEach(row => {
                    row.addEventListener('click', async function() {
                        document.querySelectorAll('.chat-row').forEach(r => r.classList.remove('selected'));
                        this.classList.add('selected');

                        const csIdx = this.dataset.csIdx;
                        
                        try {
                            const response = await fetch(`get_chat_messages.php?cs_idx=${csIdx}`);
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            const data = await response.json();

                            if (data.success && data.messages.length > 0) {
                                let messagesHtml = `
                        <table class="table table-bordered inx-table inx-table-card h-100">
                            <thead class="thead-dark">
                                            <tr>
                                    <th class="text-center align-middle">대화내역</th>
                                            </tr>
                                        </thead>
                            <tbody class="chat-messages">
                                <tr>
                                    <td style="height: 100%; padding: 0;">
                                        <div class="chat-container">
                                `;

                                data.messages.forEach(message => {
                                    messagesHtml += `
                                                <div class="message-bubble ${message.is_bot ? 'bot-bubble' : 'user-bubble'}">
                                                    ${message.content}
                                                    <small class="message-time">${message.created_at}</small>
                                                </div>
                                    `;
                                });

                                messagesHtml += `
                                        </div>
                                    </td>
                                </tr>
                                        </tbody>
                                    </table>
                                `;

                                chatbotContainer.innerHTML = messagesHtml;
                    adjustChatTableHeight();
                            } else {
                                chatbotContainer.innerHTML = errorMessage('이 세션에는 대화 내역이 없습니다.');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            chatbotContainer.innerHTML = errorMessage('오류가 발생했습니다: ' + error.message);
                        }
                    });
                });
            });

// 채팅 테이블 높이 조절 함수
function adjustChatTableHeight() {
    const sessionTable = document.getElementById('log-container');
    const chatTable = document.getElementById('chatbot-container');
    const sessionHeight = sessionTable.offsetHeight;
    
    // 여유 공간을 포함한 높이 설정
    chatTable.style.height = `${sessionHeight}px`;
    
    // 채팅 컨테이너의 높이 조절 (테이블 헤더 높이와 하단 여백 고려)
    const chatContainer = chatTable.querySelector('.chat-container');
    if (chatContainer) {
        const headerHeight = chatTable.querySelector('thead').offsetHeight;
        const containerPadding = 30; // 상하 패딩 합계
        chatContainer.style.height = `${sessionHeight - headerHeight - containerPadding}px`;
    }
}

// 페이지 로드 시 실행
document.addEventListener('DOMContentLoaded', function() {
    adjustChatTableHeight();
    
    // 창 크기 변경 시에도 높이 조절
    window.addEventListener('resize', adjustChatTableHeight);
            });
        </script>

<?php
include $_SERVER['DOCUMENT_ROOT'] . "/mng/foot.inc.php";
?>