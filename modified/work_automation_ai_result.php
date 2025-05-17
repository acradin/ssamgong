<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib.inc.php";

$_SUB_HEAD_TITLE = "업무 자동화 AI"; //헤더에 타이틀명이 없을경우 공백
$_GET['hd_pc'] = '1';//PC hd 메뉴있음1, 메뉴없음 공백
$_GET['hd_num'] = '1';//모바일 hd 1~n까지 있음
$_GET['bt_menu'] = '1'; //모바일 하단메뉴 있음1, 없음 공백

include_once $_SERVER['DOCUMENT_ROOT'] . "/head.inc.php";

if(!$_SESSION['_mt_idx']){
    p_alert('로그인이 필요합니다.','./login');
}

// 세션 ID나 카테고리 ID 체크
if (!isset($_GET['session_id']) && !isset($_GET['ct_idx'])) {
    p_alert('잘못된 접근입니다.', './item_work');
}

// 디버깅을 위한 로그 추가
error_log("Received session_id: " . $_GET['session_id']);

// 챗봇 이름 가져오기
if (isset($_GET['session_id'])) {
    $chatbot = $DB->rawQueryOne("
        SELECT ct.ct_name, parent.ct_name as parent_name, ct.ct_required_point
        FROM chat_sessions cs
        JOIN category_t ct ON cs.ct_idx = ct.ct_idx
        JOIN category_t parent ON ct.parent_idx = parent.ct_idx
        WHERE cs.session_id = ?",
        [$_GET['session_id']]
    );
} else {
    $chatbot = $DB->rawQueryOne("
        SELECT ct.ct_name, parent.ct_name as parent_name, ct.ct_required_point
        FROM category_t ct
        JOIN category_t parent ON ct.parent_idx = parent.ct_idx
        WHERE ct.ct_idx = ?",
        [$_GET['ct_idx']]
    );
}

// 포인트 관련 상수 정의
define('FREE_USAGE_LIMIT', 10);         // 무료 사용 가능 횟수

// 이번 달 사용 횟수 확인 (chat_messages 테이블 기준)
$current_month_start = date('Y-m-01 00:00:00');
$current_month_end = date('Y-m-t 23:59:59');

$monthly_usage = $DB->rawQueryOne("
    SELECT COUNT(*) as usage_count
    FROM chat_messages cm
    JOIN chat_sessions cs ON cm.cs_idx = cs.cs_idx
    WHERE cs.mt_idx = ?
    AND cm.is_bot = 0
    AND cm.created_at BETWEEN ? AND ?",
    [$_SESSION['_mt_idx'], $current_month_start, $current_month_end]
);

$usage_count = (int)$monthly_usage['usage_count'];
$remaining_free = max(0, FREE_USAGE_LIMIT - $usage_count);
$categoryId = isset($_GET['ct_idx']) ? (int)$_GET['ct_idx'] : null;

// 사용자의 해당 챗봇 세션 조회
$chatSessions = $DB->rawQuery("
    SELECT cs.*, ct.ct_name
    FROM chat_sessions cs
    JOIN category_t ct ON cs.ct_idx = ct.ct_idx
    WHERE cs.mt_idx = ? AND cs.ct_idx IN (
        SELECT ct_idx FROM category_t WHERE parent_idx = ?
    )
    ORDER BY cs.created_at DESC",
    [$_SESSION['_mt_idx'], $categoryId]
);

// 세션별로 데이터 재구성
$formattedSessions = [];
foreach ($chatSessions as $session) {
    $formattedSessions[$session['session_id']] = [
        'cs_idx' => $session['cs_idx'],
        'created_at' => $session['created_at'],
        'status' => $session['status'],
        'title' => $session['title'] ?: $session['ct_name'] // title이 null이면 카테고리 이름 사용
    ];
}

?>
    <div class="wrap">
        <div class="sub_pg">
            <div class="container">
                <div class="mobile_top_itembtn">
                    <ul>
                        <li class=""><a href="https://www.ssemgong.blog/8134c529-cab1-433f-85ad-a5d22ea63609" target="_blank">소개</a></li>
                        <li class=""><a href="./item_classroom">담임</a></li>
                        <li class="on"><a href="./item_work">업무</a></li>
                        <li class="subject">
                            <a><p class="fw_600">교과</p></a>
                            <div class="subject-box">
                                <a href="./item_middle"><p>중등</p></a>
                                <a href="./item_high"><p>고등</p></a>
                            </div>
                        </li>
                        <li class=""><a href="./item_e_book">전자책</a></li>
                        <li class=""><a href="./community_communication">커뮤니티</a></li>
                    </ul>
                </div>

                <div id="ai-create-container" class="result-box">
                    <h3 class="fs_40 fw_700 mt_20"><?= htmlspecialchars($chatbot['parent_name']) ?> - <?= htmlspecialchars($chatbot['ct_name']) ?></h3>
                    
                    <div class="history-result-box">
                        <div class="history-box">
                            <span class="fs_16 fw_700 title-text">대화 내역</span>
                            <div class="box-border chat-history">
                                <button type="button" class="btn btn-light btn-sm position-absolute" 
                                        style="top: 10px; right: 10px;" 
                                        onclick="openFullscreenChat()">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </button>
                                <div class="chat-messages">
                                    <!-- 대화 내역이 여기에 동적으로 추가됨 -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="add-request-box">
                        <span class="fs_16 fw_700 title-text">추가 요청</span>
                        <div class="box-border input-box" style="border: 3px solid black;">
                            <input id="additional-request" placeholder="추가 요청사항을 입력해주세요" />
                        </div>
                    </div>
                    
                    <!-- 남은 무료 사용 횟수 표시 -->
                    <div class="action-row">
                        <div class="usage-info">
                            <div class="usage-count">
                                <span class="count"><?= $remaining_free ?></span>
                                <span class="label">회</span>
                            </div>
                            <div class="usage-text">
                                <p>이번 달 무료 사용 가능 횟수</p>
                                <?php if ($remaining_free > 0): ?>
                                    <p class="remaining"><?= $remaining_free ?>회 남았습니다.</p>
                                <?php else: ?>
                                    <p class="no-remaining">무료 사용 횟수를 모두 사용했습니다.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="action-row-btn">
                            <button type="button" class="btn-create result-page fw_500" onclick="sendAdditionalRequest()">생성하기</button>
                            <button type="button" class="btn-prev result-page fw_500" onclick="location.href='./work_automation_ai'">AI 챗봇 목록</button>
                            <button type="button" class="btn-prev result-page fw_500" onclick="showHistory()">이전 대화 내역</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* 메인 컨테이너 스타일 */
#ai-create-container {
    width: 90%;  /* result-box 클래스일 때의 너비 */
    margin: 0 auto;
}

#ai-create-container h3 {
    text-align: center;
    margin-bottom: 3rem;
}

/* 히스토리-결과 박스 스타일 */
.history-result-box {
    display: flex;
    height: 95vh; /* 높이 증가 */
    align-items: stretch;
    margin-bottom: 3rem;
}

.history-box {
    width: 100%; /* 전체 너비 사용 */
    height: 100%;
    display: flex;
    flex-direction: column;
}

/* 추가 요청 박스 스타일 */
.add-request-box {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: stretch;
}

/* 공통 박스 테두리 스타일 */
.box-border {
    flex: 1;
    min-height: 0;
    border: 3px solid #44C1CC;
    border-radius: 10px;
    padding: 1rem;
}

.input-box {
    min-height: 45px;
}

/* 제목 텍스트 스타일 */
.title-text {
    display: block;
    color: #44C1CC;
    margin-bottom: 0.8rem;
}

/* 액션 행 스타일 */
.action-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    gap: 2rem;
}

/* 사용 횟수 표시 스타일 */
.usage-info {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 25px;
    background-color: #f8f9fa;
    border-radius: 10px;
    flex: 1;
    max-width: 300px;
}

.usage-count {
    display: flex;
    align-items: baseline;
    gap: 5px;
}

.usage-count .count {
    font-size: 2.8rem;
    font-weight: 700;
    color: #44C1CC;
}

.usage-count .label {
    font-size: 1.6rem;
    color: #44C1CC;
}

.usage-text {
    text-align: left;
}

.usage-text p {
    margin: 0;
    font-size: 1.4rem;
    color: #666;
}

.usage-text .remaining {
    color: #44C1CC;
    font-weight: 500;
    margin-top: 3px;
}

.usage-text .no-remaining {
    color: #dc3545;
    font-weight: 500;
    margin-top: 3px;
}

/* 생성하기 버튼 스타일 */
.btn-create.result-page {
    width: 120px;
    padding: 1rem;
    font-size: 1.6rem;
    background-color: #44C1CC;
    color: #fff;
    border: 0;
    border-radius: 100px;
    text-align: center;
    flex-shrink: 0; /* 버튼 크기 고정 */
}

.btn-create:hover {
    background-color:rgb(24, 149, 161);
}

/* 목록 버튼 스타일 */
.btn-prev.result-page {
    width: 120px;
    padding: 1rem;
    font-size: 1.6rem;
    background-color: #f0f0f0;
    color: #4c4c4c;
    border: 0;
    border-radius: 100px;
    text-align: center;
    flex-shrink: 0; /* 버튼 크기 고정 */
}

.btn-prev.result-page:hover {
    background-color: rgba(0,0,0,0.15);
    color: #4c4c4c;
    box-shadow: 0 4px 16px rgba(27, 167, 180, 0.10);
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
}

/* textarea 스타일 */
#additional-request {
    width: 100%;
    height: 100%;
    border: none;
    resize: none;
    font-size: 1.6rem;
    line-height: 1.5;
    padding: 0;
    font-family: inherit;
    outline: none; /* 포커스 시 테두리 제거 */
}

#additional-request::placeholder {
    color: #DBDBDB;
}

/* 채팅 스타일 */
.chat-history {
    position: relative;  /* 버튼의 absolute 포지셔닝을 위한 기준점 */
}

.chat-messages {
    height: 100%;
    overflow-y: auto;
    padding-top: 20px;  /* 버튼과 겹치지 않도록 상단 여백 추가 */
}

/* 기존 스크롤바 스타일을 .chat-messages에도 적용 */
.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-thumb {
    background-color: #CCCCCC;
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-track {
    background-color: transparent;
}

.chat-message {
    margin-bottom: 1rem;
    padding: 1rem;
    border-radius: 8px;
    max-width: 85%;
    font-size: 18px;
    line-height: 1.5;
    letter-spacing: 0.2px;
}

.user-message {
    background-color: #e6f3f5;
    margin-left: auto;
}

.ai-message {
    background-color: #f5f5f5;
    margin-right: auto;
}

.system-message {
    background-color: #f8f9fa;
    margin: 1rem auto;
    max-width: 100%;
    border: 1px solid #dee2e6;
}

.system-message .message-content {
    color: #6c757d;
}

.message-time {
    font-size: 1.2rem;
    color: #888;
    margin-top: 0.5rem;
    text-align: right;
}

/* 스크롤바 스타일 */
.chat-history::-webkit-scrollbar,
.chat-result::-webkit-scrollbar {
    width: 6px;
}

.chat-history::-webkit-scrollbar-thumb,
.chat-result::-webkit-scrollbar-thumb {
    background-color: #CCCCCC;
    border-radius: 3px;
}

.chat-history::-webkit-scrollbar-track,
.chat-result::-webkit-scrollbar-track {
    background-color: transparent;
}

/* Firefox용 스크롤바 스타일 */
.chat-history,
.chat-result {
    scrollbar-width: thin;
    scrollbar-color: #CCCCCC transparent;
}


/* 채팅 세션 아이템 스타일 */
.chat-session-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.session-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.session-date {
    color: #666;
    font-size: 0.9em;
}

.session-status {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.8em;
}

.session-status.active {
    background-color: #e3f2fd;
    color: #1976d2;
}

.session-status.completed {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.session-status.error {
    background-color: #ffebee;
    color: #c62828;
}

.session-variables {
    margin: 10px 0;
}

.variable-row {
    display: flex;
    margin: 5px 0;
    font-size: 0.95em;
}

.variable-name {
    font-weight: 500;
    margin-right: 10px;
    min-width: 120px;
}

.session-actions {
    text-align: right;
    margin-top: 10px;
}

.btn-view {
    padding: 5px 15px;
    border: 1px solid #1ba7b4;
    border-radius: 4px;
    background: none;
    color: #1ba7b4;
    font-size: 0.9em;
}

.btn-view:hover {
    background-color: #1ba7b4;
    color: #fff;
}

 /* ───────── 카드 ───────── */
 .qc-card{
        width:100%;
        border-radius:8px;
        padding:24px 18px 28px;
        font-family:'Pretendard','Apple SD Gothic Neo',sans-serif;
        font-size:18px;
        line-height:1.55;
    }
    
    /* ───────── 제목 ───────── */
    .qc-card h1{
        margin:0 0 22px;
        font-size:23px;
        font-weight:700;
        color:#1BA7B4;
        text-align:center;
    }
    .qc-card h2{
        margin:22px 0 12px;
        font-size:19px;
        font-weight:700;
        color:#1BA7B4;
    }
    
    /* ───────── 본문 리스트 ───────── */
    .qc-card .qc-ul{padding-left:18px;margin:0 0 12px;}
    .qc-card .qc-li{list-style-type:'◇ ';margin-bottom:8px;}
    .qc-card p{margin:0 0 8px;}
    
    /* ───────── 선택지 ①~⑤ ───────── */
    .qc-card .qc-options,
    .qc-card .qc-options2{padding-left:0;margin:0 0 12px;}
    .qc-card .qc-options li,
    .qc-card .qc-options2 li{
        list-style:none;
        margin-left:0;
    }
    
    /* ───────── "<보기>" 직사각형 ───────── */
    .qc-option-box{
        position:relative;
        border:2px solid #1BA7B4;
        border-radius:6px;
        padding:24px 18px 16px;
        margin:18px 0;
    }
    .qc-option-box::before{
        content:'<보기>';
        position:absolute;
        top:-10px;                 /* 라벨을 약간 더 위로 */
        left:50%;
        transform:translateX(-50%);
        padding:0 12px;
        font-size:13px;
        font-weight:600;
        color:#1BA7B4;
        background:#f5f5f5;           /* 페이지 배경색과 동일하게 */
        z-index:2;                 /* 가리는 사각형보다 위 */
        line-height:1;
    }

    .qc-option-list{margin:0;padding-left:18px;}
    .qc-option-list li{
        list-style-type:'◇ ';
        margin-bottom:8px;
    }
    
    /* ───────── 정답표 ───────── */
    .qc-card table{
        width:100%;
        border:1px solid #555;
        border-collapse:collapse;
        margin:6px 0 20px;
    }
    .qc-card th, .qc-card td{
        border:1px solid #333;
        padding:6px 0;
        text-align:center;
    }
    .qc-card th{background:#aaa;font-weight:500;}
    
    /* ───────── 해설 ───────── */
    .qc-expl-title{margin:0 0 10px;font-size:19px;font-weight:700;color:#1BA7B4;}
    .qc-card .qc-expl p{margin-bottom:10px;font-size:17px;color:#555;}

        /* ───────── 생활기록부 섹션 박스 ───────── */
    .life-record-section {
    margin-top: 30px;
    padding: 24px 24px 28px;
    background-color: #ffffff;
    border: 2px solid #1BA7B4;
    border-radius: 10px;
    box-shadow: 2px 2px 8px rgba(0,0,0,0.06);
    }

    /* ───────── 섹션 제목 ───────── */
    .life-record-section-title {
    font-size: 17px;
    font-weight: 700;
    color: #1BA7B4;
    margin-bottom: 18px;
    border-bottom: 2px solid #1BA7B4;
    padding-bottom: 6px;
    letter-spacing: -0.2px;
    }

    /* ───────── 정보 테이블 ───────── */
    .life-record-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    background-color: #fcfcfc;
    }

    .life-record-table th,
    .life-record-table td {
    border: 1px solid #bbb;
    padding: 10px 12px;
    vertical-align: top;
    text-align: left;
    line-height: 1.6;
    color: #333;
    word-break: keep-all;
    }

    /* ───────── 제목 셀 강조 ───────── */
    .life-record-table th {
    background-color: #eaf6f8;
    color: #1BA7B4;
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
    }

    /* ───────── 줄 간격 및 폰트 안정화 ───────── */
    .life-record-table td {
    background-color: #fff;
    }

    /* ───────── 인쇄용 대비 보정 (선택적) ───────── */
    @media print {
    .life-record-section {
        border: 1px solid #000;
        box-shadow: none;
    }

    .life-record-section-title {
        color: #000;
        border-color: #000;
    }

    .life-record-table th {
        background-color: #ddd !important;
        color: #000 !important;
    }

/* 전체화면 버튼 스타일 */
.fullscreen-button {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.1);
    border: none;
    border-radius: 5px;
    padding: 8px;
    cursor: pointer;
    transition: background-color 0.3s;
    z-index: 1000;
}

.fullscreen-button:hover {
    background: rgba(0, 0, 0, 0.2);
}

/* 전체화면 모드 스타일 */
.chat-history.fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 9999;
    margin: 0;
    border-radius: 0;
    background: white;
}

/* 전체화면 아이콘 변경 */
.fullscreen-button.active i::before {
    content: "\f066"; /* fa-compress 아이콘 */
}

/* 전체화면 채팅 스타일 */
.chat-history-fullscreen {
    height: calc(100vh - 60px); /* 모달 헤더 높이 고려 */
    overflow-y: auto;
    padding: 20px 30px; /* 좌우 여백 증가 */
    background-color: #fff;
}

.modal-body {
    padding: 20px !important; /* 모달 바디에 패딩 추가 */
}

/* 채팅 메시지 스타일 수정 */
.chat-message {
    margin-bottom: 1.5rem; /* 메시지 간 간격 증가 */
    padding: 1rem 1.5rem; /* 메시지 내부 여백 증가 */
    border-radius: 8px;
    max-width: 85%;
    font-size: 18px;
    line-height: 1.5;
    letter-spacing: 0.2px;
}

/* 모달 헤더 스타일 개선 */
.modal-header {
    padding: 1rem 1.5rem;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-header .close {
    padding: 1rem;
    margin: -1rem -1rem -1rem auto;
    font-size: 1.5rem;
    opacity: .5;
    transition: opacity 0.2s;
}

.modal-header .close:hover {
    opacity: 1;
}

</style>

<script>
// 페이지 로드 시 채팅 내역 및 결과 로드
document.addEventListener('DOMContentLoaded', function() {
    loadChatHistory();

    // 엔터키로 추가 요청 전송
    const input = document.getElementById('additional-request');
    if (input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendAdditionalRequest();
            }
        });
    }

    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const maxSize = 5 * 1024 * 1024; // 10MB
            if (e.target.files[0] && e.target.files[0].size > maxSize) {
                alert('파일 크기는 5MB를 초과할 수 없습니다.');
                e.target.value = ''; // 파일 선택 해제
            }
        });
    }

    // 전체화면 기능 추가
    const chatHistory = document.querySelector('.chat-history');
    const fullscreenBtn = document.querySelector('.fullscreen-button');

    fullscreenBtn.addEventListener('click', function() {
        if (!document.fullscreenElement) {
            // 전체화면으로 전환
            if (chatHistory.requestFullscreen) {
                chatHistory.requestFullscreen();
            } else if (chatHistory.webkitRequestFullscreen) {
                chatHistory.webkitRequestFullscreen();
            } else if (chatHistory.msRequestFullscreen) {
                chatHistory.msRequestFullscreen();
            }
            chatHistory.classList.add('fullscreen');
            fullscreenBtn.classList.add('active');
        } else {
            // 전체화면 종료
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
            chatHistory.classList.remove('fullscreen');
            fullscreenBtn.classList.remove('active');
        }
    });

    // 전체화면 변경 이벤트 감지
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.addEventListener('mozfullscreenchange', handleFullscreenChange);
    document.addEventListener('MSFullscreenChange', handleFullscreenChange);

    function handleFullscreenChange() {
        if (!document.fullscreenElement && 
            !document.webkitFullscreenElement && 
            !document.mozFullScreenElement && 
            !document.msFullscreenElement) {
            chatHistory.classList.remove('fullscreen');
            fullscreenBtn.classList.remove('active');
        }
    }
});

function loadChatHistory() {
    const sessionId = new URLSearchParams(window.location.search).get('session_id');
    const ctIdx = new URLSearchParams(window.location.search).get('ct_idx');
    
    console.log('Loading chat history with:', { sessionId, ctIdx });
    
    // API 호출하여 채팅 내역 가져오기
    $.ajax({
        url: 'get_chat_history.php',
        type: 'GET',
        data: { 
            session_id: sessionId,
            ct_idx: ctIdx
        },
        dataType: 'json',
        success: function(response) {
            console.log('Chat history response:', response);
            if (response.success) {
                updateChatUI(response.data);
            } else {
                console.error('Failed to load chat history:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Chat history load error:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
        }
    });
}

function updateChatUI(data) {
    const historyContainer = document.querySelector('.chat-history .chat-messages');
    const fullscreenContainer = document.querySelector('.chat-history-fullscreen');
    
    if (data.history) {
        // 채팅 내용 생성
        const chatContent = data.history.map(msg => `
            <div class="chat-message ${msg.is_bot ? 'ai-message' : 'user-message'}">
                <div class="message-content">${removeBackslashBeforeQuote(msg.content)}</div>
                <div class="message-time">${msg.created_at}</div>
            </div>
        `).join('');
        
        // 일반 채팅창 업데이트 (버튼은 그대로 두고 메시지만 업데이트)
        historyContainer.innerHTML = chatContent;
        
        // 전체화면 모달이 열려있는 경우 해당 내용도 업데이트
        if (document.getElementById('fullscreenChatModal').classList.contains('show')) {
            fullscreenContainer.innerHTML = chatContent;
        }
    }

    // 스크롤 최하단으로
    historyContainer.scrollTop = historyContainer.scrollHeight;
    if (fullscreenContainer) {
        fullscreenContainer.scrollTop = fullscreenContainer.scrollHeight;
    }
}

function sendAdditionalRequest() {
    const request = document.getElementById('additional-request').value.trim();
    if (!request) return;
    
    const sessionId = new URLSearchParams(window.location.search).get('session_id');
    const ctIdx = new URLSearchParams(window.location.search).get('ct_idx');
    const remainingFree = <?= $remaining_free ?>;
    const requiredPoint = getRequiredPointByCategoryId(ctIdx);

    //if (remainingFree <= 0) {
    //    if (!confirm(`무료 사용 횟수를 모두 사용했습니다. ${requiredPoint}포인트가 차감됩니다. 계속하시겠습니까?`)) {
    //        return;
    //    }
    //}
    
    $.ajax({
        url: 'process_additional_request.php',
        type: 'POST',
        data: {
            session_id: sessionId,
            ct_idx: ctIdx,
            request: request
        },
        dataType: 'json',
        beforeSend: function() {
            $('#splinner_modal').modal('show');
        },
        success: function(response) {
            $('#splinner_modal').modal('hide');
            if (response.success) {
                document.getElementById('additional-request').value = '';
                loadChatHistory();
                // 페이지 새로고침하여 남은 횟수 업데이트
                location.reload();
            } else {
                if (response.redirect) {
                    jalert(response.message, function() {
                        location.href = response.redirect;
                    });
                } else {
                    jalert(response.message || '오류가 발생했습니다.');
                }
            }
        },
        error: function(xhr, status, error) {
            $('#splinner_modal').modal('hide');
            console.error('추가 요청 실패:', error);
            jalert('서버 오류가 발생했습니다.');
        }
    });
}

// 이전 대화 모달 표시
function showHistory() {
    $('#historyModal').modal('show');
}

function removeBackslashBeforeQuote(str) {
    // \"만 "로 변환
    const txt = document.createElement('textarea');
    txt.innerHTML = str.replace(/\\"/g, '"');
    return txt.value;
}

// 세션 상세 보기
function viewSession(sessionId) {
    const categoryId = <?= (int)$categoryId ?>;
    location.href = `work_automation_ai_result.php?session_id=${sessionId}&ct_idx=${categoryId}`;
}

function getRequiredPointByCategoryId(categoryId) {
    const subCategories = <?= json_encode($chatbot) ?>;
    console.log('subCategories');
    console.log(subCategories);
    console.log('categoryId');
    console.log(categoryId);

    // subCategories가 배열이 아니라 객체일 때
    if (subCategories && typeof subCategories === 'object' && !Array.isArray(subCategories)) {
        // ct_idx가 있을 때만 비교
        if (subCategories.ct_idx && subCategories.ct_idx == categoryId) {
            return subCategories.ct_required_point;
        }
        // ct_idx가 없으면 그냥 ct_required_point 반환 (단일 카테고리라면)
        if (!subCategories.ct_idx) {
            return subCategories.ct_required_point;
        }
        return null;
    }

    // 배열일 경우(혹시라도)
    if (Array.isArray(subCategories)) {
        const match = subCategories.find(item => item.ct_idx == categoryId);
        return match ? match.ct_required_point : null;
    }

    return null;
}

// 전체화면 모달 관련 함수
function openFullscreenChat() {
    const originalChat = document.querySelector('.chat-history');
    const fullscreenChat = document.querySelector('.chat-history-fullscreen');
    
    // 현재 채팅 내용을 전체화면 모달로 복사
    fullscreenChat.innerHTML = originalChat.innerHTML;
    
    // 전체화면 버튼 제거 (모달에서는 필요 없음)
    const fullscreenBtn = fullscreenChat.querySelector('button');
    if (fullscreenBtn) {
        fullscreenBtn.remove();
    }
    
    // 모달 표시
    const modal = new bootstrap.Modal(document.getElementById('fullscreenChatModal'));
    modal.show();
    
    // 스크롤 최하단으로
    fullscreenChat.scrollTop = fullscreenChat.scrollHeight;
}

</script>

<!-- 이전 대화 모달 -->
<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">이전 대화 목록</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php foreach ($formattedSessions as $sessionId => $session): ?>
                    <div class="chat-session-item">
                        <div class="session-header">
                            <span class="session-date"><?= date('Y-m-d H:i', strtotime($session['created_at'])) ?></span>
                            <span class="session-status <?= $session['status'] ?>"><?= $session['status'] === 'active' ? '진행중' : ($session['status'] === 'completed' ? '완료' : '오류') ?></span>
                        </div>
                        <div class="session-title">
                            <h4><?= htmlspecialchars($session['title']) ?></h4>
                        </div>
                        <div class="session-actions">
                            <button type="button" class="btn-view" onclick="viewSession('<?= $sessionId ?>')">대화 보기</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- 전체화면 모달 추가 -->
<div class="modal fade" id="fullscreenChatModal" tabindex="-1" aria-labelledby="fullscreenChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullscreenChatModalLabel">대화 내역</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="chat-history-fullscreen">
                    <!-- 대화 내역이 여기에 복사됨 -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include $_SERVER['DOCUMENT_ROOT'] . "/foot.inc.php";
include $_SERVER['DOCUMENT_ROOT'] . "/tail.inc.php";
?>
