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
        SELECT ct.ct_name, parent.ct_name as parent_name
        FROM chat_sessions cs
        JOIN category_t ct ON cs.ct_idx = ct.ct_idx
        JOIN category_t parent ON ct.parent_idx = parent.ct_idx
        WHERE cs.session_id = ?",
        [$_GET['session_id']]
    );
} else {
    $chatbot = $DB->rawQueryOne("
        SELECT ct.ct_name, parent.ct_name as parent_name
        FROM category_t ct
        JOIN category_t parent ON ct.parent_idx = parent.ct_idx
        WHERE ct.ct_idx = ?",
        [$_GET['ct_idx']]
    );
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
                                <!-- 대화 내역이 여기에 동적으로 추가됨 -->
                            </div>
                        </div>
                        <div class="result-box">
                            <span class="fs_16 fw_700 title-text">결과</span>
                            <div class="box-border chat-result">
                                <!-- AI 응답 결과가 여기에 동적으로 추가됨 -->
                            </div>
                        </div>
                    </div>

                    <div class="add-request-box">
                        <span class="fs_16 fw_700 title-text">추가 요청</span>
                        <div class="box-border">
                            <input id="additional-request" placeholder="추가 요청사항을 입력해주세요" />
                        </div>
                    </div>

                    <button type="button" class="btn-create result-page fw_500" onclick="sendAdditionalRequest()">생성하기</button>
                </div>
            </div>
        </div>
    </div>

<style>
/* 메인 컨테이너 스타일 */
#ai-create-container {
    width: 75%;  /* result-box 클래스일 때의 너비 */
    margin: 0 auto;
}

#ai-create-container h3 {
    text-align: center;
    margin-bottom: 3rem;
}

/* 히스토리-결과 박스 스타일 */
.history-result-box {
    display: flex;
    height: 40vh;
    align-items: stretch;
    gap: 3rem;
    margin-bottom: 3rem;
}

.history-box {
    width: calc(30% - 1.5rem);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.result-box {
    width: calc(70% - 1.5rem);
    height: 100%;
    display: flex;
    flex-direction: column;
}

/* 추가 요청 박스 스타일 */
.add-request-box {
    width: 100%;
    height: 8vh;
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

/* 제목 텍스트 스타일 */
.title-text {
    display: block;
    color: #44C1CC;
    margin-bottom: 0.8rem;
}

/* 생성하기 버튼 스타일 */
.btn-create.result-page {
    width: 12%;
    margin: 1.5rem 0 0 auto;
    padding: 0.9rem;
    font-size: 1.6rem;
    background-color: #44C1CC;
    color: #fff;
    border: 0;
    border-radius: 100px;
    text-align: center;
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
.chat-history, .chat-result {
    overflow-y: auto;
    height: 100%;
}

.chat-message {
    margin-bottom: 1rem;
    padding: 1rem;
    border-radius: 8px;
    max-width: 80%;
    font-size: 1.6rem;
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
    background-color: #44C1CC;
    border-radius: 3px;
}

.chat-history::-webkit-scrollbar-track,
.chat-result::-webkit-scrollbar-track {
    background-color: #f1f1f1;
    border-radius: 3px;
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
    const historyContainer = document.querySelector('.chat-history');
    const resultContainer = document.querySelector('.chat-result');
    
    // 채팅 내역 업데이트
    if (data.history) {
        historyContainer.innerHTML = data.history.map(msg => `
            <div class="chat-message ${msg.is_bot ? 'ai-message' : 'user-message'}">
                <div class="message-content">${msg.content}</div>
                <div class="message-time">${msg.created_at}</div>
            </div>
        `).join('');
    }

    // 결과(최신 AI 결과) 표시
    if (data.last_ai_result) {
        resultContainer.innerHTML = data.last_ai_result;
    } else {
        resultContainer.innerHTML = '';
    }

    // 스크롤을 최하단으로
    historyContainer.scrollTop = historyContainer.scrollHeight;
    resultContainer.scrollTop = resultContainer.scrollHeight;
}

function sendAdditionalRequest() {
    const request = document.getElementById('additional-request').value.trim();
    if (!request) return;
    
    const sessionId = new URLSearchParams(window.location.search).get('session_id');
    const ctIdx = new URLSearchParams(window.location.search).get('ct_idx');
    
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
            } else {
                jalert(response.message || '오류가 발생했습니다.');
            }
        },
        error: function(xhr, status, error) {
            $('#splinner_modal').modal('hide');
            console.error('추가 요청 실패:', error);
            jalert('서버 오류가 발생했습니다.');
        }
    });
}
</script>

<?php
include $_SERVER['DOCUMENT_ROOT'] . "/foot.inc.php";
include $_SERVER['DOCUMENT_ROOT'] . "/tail.inc.php";
?>
