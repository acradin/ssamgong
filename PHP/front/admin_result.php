<?php
require_once '../config/database.php';

// 세션 시작
session_start();
$_SESSION['_mt_idx'] = 2;  // 테스트용 관리자 ID

// 페이지네이션 기본 변수 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$total_pages = 1; // 기본값 설정
$total_records = 0; // 기본값 설정

// 데이터베이스 연결
$db = Database::getInstance()->getConnection();

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

    // 기본 쿼리 작성
    $base_query = "FROM chat_sessions cs
        LEFT JOIN member_t m ON cs.mt_idx = m.mt_idx
        LEFT JOIN category_t ct ON cs.ct_idx = ct.ct_idx
        LEFT JOIN category_t pct ON ct.parent_idx = pct.ct_idx";

    // 조건절 설정
    $where = "";
    $params = [];
    if (isset($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $where = "WHERE DATE(cs.created_at) BETWEEN ? AND ?";
        $params[] = $_GET['start_date'];
        $params[] = $_GET['end_date'];
    }

    // 전체 개수 조회
    $count_query = "SELECT COUNT(*) as total " . $base_query . ($where ? " " . $where : "");
    $total_result = $db->rawQuery($count_query, $params);
    $total_records = $total_result[0]['total'];
    $total_pages = ceil($total_records / $limit);

    // 실제 데이터 조회
    $query = "SELECT 
        cs.cs_idx,
        cs.session_id,
        cs.created_at,
        m.mt_id,
        m.mt_nickname,
        '쌤공 AI' as bot_name,
        CASE 
            WHEN pct.ct_name IS NOT NULL THEN CONCAT(pct.ct_name, ' > ', ct.ct_name)
            WHEN ct.ct_name IS NOT NULL THEN ct.ct_name
            ELSE '카테고리 없음'
        END as category_name
    " . $base_query;

    if ($where) {
        $query .= " " . $where;
    }

    $query .= " ORDER BY cs.created_at DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;

    $histories = $db->rawQuery($query, $params);

} catch (Exception $e) {
    $error_message = $e->getMessage();
    $histories = [];
}

// 페이지 그룹 계산
$page_group = ceil($page / 10);
$start_page = ($page_group - 1) * 10 + 1;
$end_page = min($start_page + 9, $total_pages);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>메인페이지</title>
        <link rel="stylesheet" href="../css/boot_custom.css">
        <link rel="stylesheet" href="../css/design.css">
        <link rel="stylesheet" href="../css/custom.css">
        <link rel="stylesheet" href="../css/default_dev.css">
        <link rel="stylesheet" href="../css/flatpickr.min.css">
        <link rel="stylesheet" href="../css/admin.css">
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.bundle.min.js"></script>
        <script src="../js/flatpickr.min.js"></script>
        <script src="../js/flatpickr.ko.js"></script>
        <style>
            .pagination-container {
                margin-top: 30px;
                margin-bottom: 50px;
                display: flex;
                justify-content: center;
            }
            .pagination {
                display: flex;
                gap: 10px;
                align-items: center;
            }
            .page-link {
                padding: 8px 12px;
                color: #666;
                text-decoration: none;
                min-width: 40px;
                text-align: center;
                border: none;
                font-size: 14px;
            }
            .page-link:hover {
                color: #44C1CC;
                text-decoration: none;
            }
            .page-link.active {
                background-color: #44C1CC;
                color: white;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <div class="d-flex">
            <!-- 사이드바 -->
            <div id="sidebar">
                <div id="adminLogo" class="logo mb-5">
                    <img src="../img/admin_logo.svg" alt="쌤공" class="mr-1">
                </div>
                <div class="menu">
                    <a href="./admin_managebot.php" class="menu-item">
                        <img src="../img/dashboard.svg" alt="">
                        챗봇 관리
                    </a>
                    <a href="./admin_result.php" class="menu-item active">
                        <img src="../img/dashboard.svg" alt="">
                        결과 조회
                    </a>
                    <a href="./admin_point.php" class="menu-item">
                        <img src="../img/dashboard.svg" alt="">
                        포인트 조회
                    </a>
                    <a href="./admin_createbot.php" class="menu-item">
                        <img src="../img/dashboard.svg" alt="">
                        챗봇 생성
                    </a>
                </div>
            </div>
    
            <!-- 메인 컨텐츠 -->
            <div id="main-container">
                <div class="flex-bw mt_10 mb_50">
                    <div class="flex-c">
                        <a href="./" class="mr_20 logo"><img src="../img/logo.svg"></a>
                        <div class="relative">
                            <form class="sch_ip align-items-center" method="get" name="frm_search" id="frm_search" action="./search_update" novalidate="novalidate">
                                <select id="search_select" name="search_option" style="    margin-left: 5px;">
                                    <option value="title">제목</option>
                                    <option value="content">내용</option>
                                    <option value="author">작성자</option>
                                </select>
                                <input type="search" id="searchinput" name="searchinput" class="form-control fs_14 flex-fill border-0" placeholder="검색어를 입력해주세요" value="">
                                <button type="submit" class="btn btn-icon flex-shrink-0"><img src="../img/ico_search.svg" style="width:2rem;"></button>
                            </form>
                            <script>
                                $("#frm_search").validate({
                                    submitHandler: function() {
                                        return true;
                                    },
                                    rules: {
                                        searchinput: {
                                            required: true,
                                        },
                                    },
                                    messages: {
                                        searchinput: {
                                            required: "검색어를 입력해주세요",
                                        },
                                    },
                                    errorPlacement: function(error, element) {
                                        $(element)
                                            .closest("form")
                                            .find("span[for='" + element.attr("id") + "']")
                                            .append(error);
                                    },
                                });
                            </script>
                            <div class="search-bot-box" id="searchbox">
                            <div class="recent-search">
                                <h3 class="fs_17 fw_700">최근 검색어</h3>
                                <ul>
                                    <ul>
                                        <li>최근 검색어가 없습니다.</li>
                                    </ul>
                                </ul>
                            </div>
                            <div class="recommended-search">
                                <h3 class="fs_17 fw_700">추천 검색어</h3>
                                <ul>
                                    <li>
                                        <a href="./search_update?searchinput=비상연락망"><p>비상연락망</p></a>
                                    </li>
                                    <li>
                                        <a href="./search_update?searchinput=이름표"><p>이름표</p></a>
                                    </li>
                                    <li>
                                        <a href="./search_update?searchinput=학급 홈페이지"><p>학급 홈페이지</p></a>
                                    </li>
                                    <li>
                                        <a href="./search_update?searchinput=기초조사서"><p>기초조사서</p></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <script>
                        
                            document.addEventListener('DOMContentLoaded', (event) => {
                                const searchInput = document.getElementById('searchinput');
                                const searchBox = document.getElementById('searchbox');
                                const mobileSearchInput = document.getElementById('mobileSearchInput');
                                const body = document.body;
                        
                                function handleSearchInputClick(event) {
                                    console.log('Search input clicked'); // Debug log
                                    searchBox.classList.add('active');
                                    body.classList.add('no-scroll');
                                    mobileSearchInput.focus(); // 모바일 검색 input에 포커스
                                    event.stopPropagation(); // 이벤트 전파 막기
                                }
                        
                                function handleDocumentClick(event) {
                                    if (!searchBox.contains(event.target) && !searchInput.contains(event.target)) {
                                        console.log('Click outside searchbox'); // Debug log
                                        searchBox.classList.remove('active');
                                        body.classList.remove('no-scroll');
                                    }
                                }
                        
                                function handleBackButtonClick(event) {
                                    console.log('Back button clicked'); // Debug log
                                    searchBox.classList.remove('active');
                                    body.classList.remove('no-scroll');
                                    event.stopPropagation(); // 이벤트 전파 막기
                                }
                        
                                searchInput.addEventListener('click', handleSearchInputClick);
                                document.addEventListener('click', handleDocumentClick);
                            });
                        </script>
                        </div>
                    </div>
                    <div class="flex-c">
                        <!-- 로그인 -->
                        <div class="flex-c">
                            <div class="hd_profile">
                                <img src="https://ssemgong.com/uploads/mt_image1_1072_1_1743442040.png" alt="Kou" onerror="this.src=\'https://ssemgong.com/img/profile_no_img.svg\'">
                            </div>
                            <div class="hd-name">
                                <div class="flex-c">
                                    <p class="fw_300 fs_14 mr_10 lh-25">Kou</p><!-- 이름 -->
                                    <img src="../img/ic_ip_select.svg">
                                </div>
                                <ul>
                                    <li><a href="./mypage">마이페이지</a></li>
                                    <li><a href="./logout">로그아웃</a></li>
                                    <li><a href="./community_faq">고객센터</a></li>
                                </ul>
                            </div>
                            <div class="hb-point">
                                <p>0P</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-bw">
                    <h2 class="fs_28 fw_600">결과 조회</h2>
    
                    <!-- 필터 버튼 -->
                    <div id="filter-box">
                        <button class="btn-filter fs_15 fw_500 <?= (!isset($_GET['order']) || $_GET['order'] == 'latest') ? 'active' : '' ?>" 
                            onclick="location.href='?order=latest'">최신순</button>
                        <button id="date-filter" class="btn-filter fs_15 fw_500" type="button" onclick="clearDateFilter(event)">
                            <input type="text" class="input-date" 
                                placeholder="기간별" 
                                value="<?= (isset($_GET['start_date']) && isset($_GET['end_date'])) ? $_GET['start_date'] . ' ~ ' . $_GET['end_date'] : '' ?>" 
                                readonly>
                            <div class="dropdown-icon polygon"></div>
                        </button>
                        <div class="dropdown" id="filter">
                            <button class="btn-filter fs_15 fw_500" type="button" data-toggle="dropdown" aria-expanded="false">
                                <div class="dropdown-text" id="filter_text">챗봇별</div>
                                <div class="dropdown-icon polygon"></div>
                            </button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item" onclick="f_get_box_list_orderby('1');">최신순</button>
                                <button class="dropdown-item" onclick="f_get_box_list_orderby('2');">인기순</button>
                                <button class="dropdown-item" onclick="f_get_box_list_orderby('3');">조회순</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex-c gap_20">
                    <!-- 채팅 내역 테이블 -->
                    <div id="chatbot-container" class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>대화내역</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">세션을 선택해주세요.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 세션 목록 테이블 -->
                    <div id="log-container" class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>세션 ID</th>
                                    <th>사용자 정보</th>
                                    <th>챗봇 이름</th>
                                    <th>카테고리</th>
                                    <th>일시</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($histories)): ?>
                                    <?php foreach ($histories as $history): ?>
                                        <tr class="chat-row" data-cs-idx="<?= $history['cs_idx'] ?>">
                                            <td><?= htmlspecialchars($history['session_id']) ?></td>
                                            <td>
                                                닉네임: <?= htmlspecialchars($history['mt_nickname']) ?><br>
                                                ID: <?= htmlspecialchars($history['mt_id']) ?>
                                            </td>
                                            <td><?= htmlspecialchars($history['bot_name']) ?></td>
                                            <td><?= htmlspecialchars($history['category_name']) ?></td>
                                            <td><?= $history['created_at'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">세션이 없습니다.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php
                if ($total_pages > 1): 
                ?>
                <div class="pagination-container">
                    <div class="pagination">
                        <?php if ($start_page > 1): ?>
                            <!-- 첫 페이지로 -->
                            <a href="?page=1<?= isset($_GET['start_date']) && isset($_GET['end_date']) ? '&start_date='.$_GET['start_date'].'&end_date='.$_GET['end_date'] : '' ?>" class="page-link">
                                &laquo;
                            </a>
                            <!-- 이전 10페이지 -->
                            <a href="?page=<?= $start_page - 1 ?><?= isset($_GET['start_date']) && isset($_GET['end_date']) ? '&start_date='.$_GET['start_date'].'&end_date='.$_GET['end_date'] : '' ?>" class="page-link">
                                &lt;
                            </a>
                        <?php endif; ?>

                        <?php
                        for ($i = $start_page; $i <= $end_page; $i++):
                            $url_params = [];
                            $url_params[] = "page=" . $i;
                            if(isset($_GET['start_date']) && isset($_GET['end_date'])) {
                                $url_params[] = "start_date=" . $_GET['start_date'];
                                $url_params[] = "end_date=" . $_GET['end_date'];
                            }
                            $url = "?" . implode("&", $url_params);
                        ?>
                            <a href="<?= $url ?>" class="page-link <?= $i == $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($end_page < $total_pages): ?>
                            <!-- 다음 10페이지 -->
                            <a href="?page=<?= $end_page + 1 ?><?= isset($_GET['start_date']) && isset($_GET['end_date']) ? '&start_date='.$_GET['start_date'].'&end_date='.$_GET['end_date'] : '' ?>" class="page-link">
                                &gt;
                            </a>
                            <!-- 마지막 페이지로 -->
                            <a href="?page=<?= $total_pages ?><?= isset($_GET['start_date']) && isset($_GET['end_date']) ? '&start_date='.$_GET['start_date'].'&end_date='.$_GET['end_date'] : '' ?>" class="page-link">
                                &raquo;
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
            function clearDateFilter(e) {
                const dateInput = document.querySelector('.input-date');
                if(dateInput.value) {
                    e.preventDefault();
                    e.stopPropagation();
                    let currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.delete('start_date');
                    currentUrl.searchParams.delete('end_date');
                    window.location.href = currentUrl.toString();
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                const dateInput = document.querySelector('.input-date');
                
                flatpickr(".input-date", {
                    locale: "ko",
                    mode: "range",
                    dateFormat: "Y-m-d",
                    disableMobile: "true",
                    maxDate: "today",
                    monthSelectorType: "static",
                    placeholder: "기간별",
                    position: "auto right",
                    positionElement: document.querySelector('#date-filter'),
                    defaultDate: <?= (isset($_GET['start_date']) && isset($_GET['end_date'])) ? 
                        json_encode([$_GET['start_date'], $_GET['end_date']]) : 'null' ?>,
                    onChange: function(selectedDates, dateStr) {
                        if(selectedDates.length === 2) {
                            const formatDate = (date) => {
                                const year = date.getFullYear();
                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                const day = String(date.getDate()).padStart(2, '0');
                                return `${year}-${month}-${day}`;
                            };

                            const startDate = formatDate(selectedDates[0]);
                            const endDate = formatDate(selectedDates[1]);
                            
                            let currentUrl = new URL(window.location.href);
                            currentUrl.searchParams.set('start_date', startDate);
                            currentUrl.searchParams.set('end_date', endDate);
                            currentUrl.searchParams.delete('page');
                            window.location.href = currentUrl.toString();
                        }
                    },
                    onOpen: function() {
                        const btn = document.querySelector('#date-filter');
                        btn.setAttribute('aria-expanded', 'true');
                    },
                    onClose: function() {
                        const btn = document.querySelector('#date-filter');
                        btn.setAttribute('aria-expanded', 'false');
                    }
                });

                // input 클릭 시 이벤트 처리
                dateInput.addEventListener('click', function(e) {
                    if(dateInput.value) {
                        e.preventDefault();
                        e.stopPropagation();
                        clearDateFilter(e);
                    }
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                const rows = document.querySelectorAll('.chat-row');
                const chatbotContainer = document.getElementById('chatbot-container');

                // 초기 상태 메시지 설정
                const defaultMessage = `
                    <table>
                        <thead>
                            <tr>
                                <th>대화내역</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">세션을 선택해주세요.</td>
                            </tr>
                        </tbody>
                    </table>
                `;

                // 에러 메시지 템플릿
                const errorMessage = (message) => `
                    <table>
                        <thead>
                            <tr>
                                <th>대화내역</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">${message}</td>
                            </tr>
                        </tbody>
                    </table>
                `;

                rows.forEach(row => {
                    row.addEventListener('click', async function() {
                        // 선택된 행 스타일 변경
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
                                // 채팅 메시지 표시
                                let messagesHtml = `
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>대화내역</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                `;

                                data.messages.forEach(message => {
                                    messagesHtml += `
                                        <tr>
                                            <td>
                                                <div class="message-bubble ${message.is_bot ? 'bot-bubble' : 'user-bubble'}">
                                                    ${message.content}
                                                    <small class="message-time">${message.created_at}</small>
                                                </div>
                                            </td>
                                        </tr>
                                    `;
                                });

                                messagesHtml += `
                                        </tbody>
                                    </table>
                                `;

                                chatbotContainer.innerHTML = messagesHtml;
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
        </script>
    </body>
</html>