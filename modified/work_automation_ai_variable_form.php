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

// 카테고리 ID 체크
if (!isset($_GET['ct_idx'])) {
    p_alert('잘못된 접근입니다.', './item_work');
    exit;
}

$categoryId = (int)$_GET['ct_idx'];

// 상위 카테고리 정보 조회
$category = $DB->rawQueryOne("
    SELECT ct_idx, ct_name 
    FROM category_t 
    WHERE ct_idx = ?", 
    [$categoryId]
);

// 하위 카테고리 조회
$subCategories = $DB->rawQuery("
    SELECT ct_idx, ct_name, ct_required_point
    FROM category_t
    WHERE parent_idx = ? AND ct_status = 'Y'
    ORDER BY ct_order",
    [$categoryId]
);

// 첫 번째 하위 카테고리의 변수 정보 조회 (기본값)
if (!empty($subCategories)) {
    $defaultSubCategory = $subCategories[0];
    $variables = $DB->rawQuery("
        SELECT cv_idx, cv_name, cv_type, cv_description, cv_options, cv_required
        FROM chatbot_variable_t
        WHERE ct_idx = ? AND cv_status = 'Y'
        ORDER BY cv_order",
        [$defaultSubCategory['ct_idx']]
    );
}

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
        'title' => $session['title'] ?: $session['ct_name']
    ];
}
?>

<!-- Font Awesome CDN 추가 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<div class="wrap">
    <div class="sub_pg">
        <div class="container">
            <div class="main-wrapper">
                <div class="unified-container">
                    <!-- 햄버거 메뉴 버튼 -->
                    <button class="menu-toggle-btn" id="menuToggleBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <!-- 보조 메뉴 -->
                    <div class="secondary-menu" id="secondaryMenu">
                        <?php
                        // 활성화된 챗봇 목록 조회
                        $chatbots = $DB->rawQuery("
                            SELECT 
                                c.ct_idx,
                                c.ct_name,
                                cd.cd_description
                            FROM category_t c
                            LEFT JOIN chatbot_description_t cd ON c.ct_idx = cd.ct_idx
                            WHERE c.parent_idx IS NULL 
                            AND c.ct_status = 'Y'
                            ORDER BY c.ct_order ASC
                        ");

                        foreach ($chatbots as $chatbot) {
                            $isActive = (isset($_GET['ct_idx']) && $_GET['ct_idx'] == $chatbot['ct_idx']) ? 'active' : '';
                        ?>
                            <a href="./work_automation_ai_variable_form.php?ct_idx=<?= $chatbot['ct_idx'] ?>" class="secondary-menu-item <?= $isActive ?>">
                                <span><?= htmlspecialchars($chatbot['ct_name']) ?></span>
                            </a>
                        <?php } ?>

                        <!-- 메인 메뉴들 -->
                        <a href="./work_automation_ai.php" class="secondary-menu-item">
                            <span>메인으로</span>
                        </a>
                    </div>

                    <section class="chatbot-section">
                        <div class="chatbot-title"><?= htmlspecialchars($category['ct_name']) ?></div>
                        <span class="section-title-highlight"></span>

                        <!-- 하위 카테고리 탭 -->
                        <?php if (count($subCategories) > 1): ?>
                            <div class="toggle-group">
                                <?php foreach ($subCategories as $subCategory): ?>
                                    <button type="button" 
                                            class="toggle-btn <?= ($subCategory['ct_idx'] === $defaultSubCategory['ct_idx']) ? 'active' : '' ?>"
                                            data-category-id="<?= $subCategory['ct_idx'] ?>">
                                        <?= htmlspecialchars($subCategory['ct_name']) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form id="variable-form" class="chatbot-form" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="ct_idx" value="<?= $defaultSubCategory['ct_idx'] ?>">
                            <div id="variables-container">
                                <!-- 변수 필드들이 JavaScript로 여기에 추가됨 -->
                            </div>

                            <!-- 사용량 정보 -->
                            <div class="usage-info">
                                <span class="usage-count"><?= $remaining_free ?> 회</span>
                                <div>
                                    <span class="usage-text">이번 달 무료 사용 가능 횟수</span>
                                    <?php if ($remaining_free > 0): ?>
                                        <span class="usage-text"><?= $remaining_free ?>회 남았습니다.</span>
                                    <?php else: ?>
                                        <span class="usage-warning">무료 사용 횟수를 모두 사용했습니다.</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="modal-buttons">
                                <button type="submit" class="submit-button">생성하기</button>
                                <?php if (!empty($formattedSessions)): ?>
                                    <button type="button" class="cancel-button" onclick="showHistory()">이전 대화 내역</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </section>

                    <!-- 향상된 챗봇 사용방법 섹션 -->
                    <section class="enhanced-guide">
                        <h2 class="enhanced-guide-title"><i class="fas fa-lightbulb"></i> 챗봇 사용방법</h2>
                        <div class="guide-steps">
                            <div class="guide-step">
                                <div class="step-number">1</div>
                                <div class="step-icon"><i class="fas fa-edit"></i></div>
                                <div class="step-title">정보 입력</div>
                                <div class="step-description">
                                    입력 폼에 필요한 정보를 상세히 작성하세요.
                                </div>
                            </div>
                            <div class="guide-step">
                                <div class="step-number">2</div>
                                <div class="step-icon"><i class="fas fa-magic"></i></div>
                                <div class="step-title">생성하기</div>
                                <div class="step-description">
                                    <span class="step-highlight">생성하기</span> 버튼을 누르면 AI가 결과물을 생성합니다.
                                </div>
                            </div>
                            <div class="guide-step">
                                <div class="step-number">3</div>
                                <div class="step-icon"><i class="fas fa-pencil-alt"></i></div>
                                <div class="step-title">추가 요청하기</div>
                                <div class="step-description">
                                    생성된 결과물이 마음에 안든다면 <span class="step-highlight">추가로 요청</span>해서 다듬어보세요.
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

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

<!-- 파일 드롭 오버레이 -->
<div id="drop-overlay" style="display:none;">
    <div class="drop-message">파일을 내려놓으세요</div>
</div>

<!-- 스타일 및 스크립트 -->
<style>
    /* 챗봇 섹션 */
    .chatbot-section { margin: 0 auto; background: linear-gradient(to bottom, #fff, #f9fdfd); border-radius: 24px; padding: 48px 48px 40px 48px; max-width: 800px; min-width: 400px; }
    @media (max-width: 900px) {
        .chatbot-section { max-width: 100%; min-width: 0; padding: 32px 10px 24px 10px; }
    }
    .chatbot-title { font-size: 36px; font-weight: 800; text-align: center; margin-bottom: 10px; color: #000; letter-spacing: -1px; position: relative; }
    .section-title-highlight { display: block; margin: 0 auto 28px auto; width: 180px; height: 8px; background: linear-gradient(90deg, #ffe066 0%, #fff6b7 100%); border-radius: 6px; opacity: 0.7; }
    
    /* 폼 요소 */
    .chatbot-form { display: flex; flex-direction: column; }
    .form-row { display: flex; border: 1.5px solid #00a0a0; border-radius: 10px; overflow: hidden; margin-bottom: 18px; background: #fff; align-items: center; box-shadow: 0 1px 3px rgba(0,150,150,0.05); }
    .form-label { background: linear-gradient(135deg, #00a0a0, #00b8b8); color: white; width: 150px; padding: 14px; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 16px; flex-shrink: 0; transition: background 0.2s, color 0.2s; }
    .form-input { flex: 1; padding: 12px; border: none; outline: none; font-size: 16px; background: #fff; color: #333; }
    .form-row.optional { border: 1.5px solid #e6c74c; background: linear-gradient(to right, #fffdf0, #fffef7); border-width: 1.5px; box-shadow: 0 2px 8px rgba(230, 199, 76, 0.08); }
    .form-row.optional .form-label { background: linear-gradient(135deg, #e6c74c, #f0d774); color: #5a4e00; font-weight: 700; }
    .form-row.optional .form-input { background: linear-gradient(to right, #fffdf0, #fffef7); color: #8a6e00; border-left: none; }
    
    /* 사용량 정보 */
    .usage-info { background: #f5f8f8; border-radius: 12px; padding: 20px 24px; margin: 28px 0 24px 0; display: flex; align-items: center; gap: 24px; border: 1.5px solid #e0eeee; }
    .usage-count { font-size: 32px; font-weight: 800; color: #00a0a0; margin-right: 6px; }
    .usage-text { font-size: 15px; color: #445; }
    .usage-warning { color: #e57373; font-size: 15px; margin-top: 4px; display: block; }
    
    /* 버튼 */
    .modal-buttons { display: flex; justify-content: center; gap: 18px; margin-top: 18px; }
    .submit-button, .cancel-button { padding: 16px 38px; border-radius: 25px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; font-size: 18px; }
    .submit-button { background: linear-gradient(135deg, #00a0a0, #00b8b8); color: white; border: none; box-shadow: 0 2px 8px rgba(0,150,150,0.2); }
    .submit-button:hover { background: linear-gradient(135deg, #009090, #00a8a8); box-shadow: 0 3px 10px rgba(0,150,150,0.3); }
    .cancel-button { background-color: #fff; color: #00a0a0; border: 1.5px solid #00a0a0; }
    .cancel-button:hover { background-color: #f0f9f9; }
    
    /* 메인 래퍼 */
    .main-wrapper {
        background: linear-gradient(to bottom, #f9fdfd, #e6f7f7);
        border-radius: 30px;
        margin: 30px auto;
        padding: 30px 0;
        box-shadow: 0 10px 30px rgba(0, 160, 160, 0.08);
        max-width: 1200px;
    }
    
    /* 부가 메뉴 스타일 */
    .secondary-menu {
        position: absolute;
        top: 15px;
        right: 20px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(5px);
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 160, 160, 0.15);
        padding: 15px;
        z-index: 100;
        display: flex;
        flex-direction: column;
        gap: 10px;
        transition: all 0.3s ease;
    }
    .secondary-menu-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #333;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 10px;
        transition: all 0.2s;
        font-weight: 500;
        font-size: 14px;
    }
    .secondary-menu-item:hover {
        background: #e0f7fa;
        color: #00a0a0;
    }
    .secondary-menu-item i {
        font-size: 16px;
        width: 20px;
        text-align: center;
        color: #00a0a0;
    }
    
    /* 통합 컨테이너 스타일 */
    .unified-container {
        background: linear-gradient(145deg, #ffffff, #f6fbfc, #e0f5f5);
        border-radius: 30px;
        box-shadow: 0 15px 40px rgba(0, 160, 160, 0.1);
        margin: 20px auto;
        padding: 40px;
        position: relative;
        overflow: hidden;
        max-width: 880px;
    }
    .unified-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 8px;
        background: linear-gradient(90deg, #00a0a0, #4db6ac);
    }
    
    /* 향상된 가이드 스타일 */
    .enhanced-guide {
        margin-top: 40px;
        padding: 30px;
        background: rgba(255, 255, 255, 0.7);
        border-radius: 20px;
        border: 1px solid rgba(0, 160, 160, 0.1);
    }
    .enhanced-guide-title {
        font-size: 24px;
        font-weight: 700;
        color: #008080;
        text-align: center;
        margin-bottom: 25px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .enhanced-guide-title i {
        color: #00a0a0;
        font-size: 26px;
    }
    .enhanced-guide-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, #ffe066, #fff6b7);
        border-radius: 3px;
    }
    .guide-steps {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 30px;
    }
    .guide-step {
        flex: 1;
        min-width: 200px;
        background: #fff;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0, 160, 160, 0.08);
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .step-number {
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 30px;
        height: 30px;
        background: linear-gradient(135deg, #00a0a0, #00b8b8);
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }
    .step-icon {
        font-size: 32px;
        color: #00a0a0;
        margin-bottom: 15px;
    }
    .step-title {
        font-weight: 700;
        color: #333;
        margin-bottom: 10px;
        font-size: 16px;
    }
    .step-description {
        color: #666;
        font-size: 14px;
        line-height: 1.6;
    }
    .step-highlight {
        display: inline-block;
        background: linear-gradient(90deg, rgba(0, 160, 160, 0.1), transparent);
        padding: 2px 6px;
        border-radius: 4px;
        color: #00a0a0;
        font-weight: 600;
    }
    
    /* 햄버거 메뉴 스타일 추가 */
    .menu-toggle-btn {
        position: absolute;
        top: 15px;
        right: 20px;
        width: 40px;
        height: 40px;
        background: #00a0a0;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 101;
        box-shadow: 0 3px 10px rgba(0, 160, 160, 0.2);
        border: none;
    }
    .menu-toggle-btn:hover {
        background: #008a8a;
    }
    .menu-toggle-btn i {
        font-size: 18px;
    }
    .secondary-menu {
        max-height: 0;
        padding: 0 15px;
        overflow: hidden;
        opacity: 0;
        top: 65px;
        right: 20px;
        margin-top: 0;
        transition: all 0.3s ease;
        pointer-events: none;
    }
    .secondary-menu.open {
        max-height: 400px;
        padding: 15px;
        opacity: 1;
        margin-top: 0;
        pointer-events: all;
    }
    
    /* 채팅 대화 내역 스타일 */
    .chat-history {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #f0f5f6;
        z-index: 1000;
        overflow-y: auto;
    }
    
    .chat-history::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(to right, #00a0a0, #4db6ac);
        z-index: 1001;
    }
    
    .chat-container {
        background-color: white;
        border-radius: 10px;
        width: 90%;
        max-width: 800px;
        margin: 60px auto 30px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        position: relative;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding-bottom: 20px;
    }
    
    .chat-header {
        background: white;
        padding: 20px;
        color: #333;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        border-bottom: 1px solid #eee;
    }
    
    .chat-title {
        font-size: 24px;
        font-weight: 700;
        text-align: center;
    }
    
    .chat-history-content {
        padding: 30px;
        overflow-y: auto;
        flex-grow: 1;
    }
    
    .chat-message {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
    }
    
    .message-header {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }
    
    .message-time {
        color: #888;
        font-size: 12px;
        margin-left: 10px;
    }
    
    .message-bubble {
        padding: 12px 15px;
        border-radius: 10px;
        max-width: 80%;
        line-height: 1.5;
    }
    
    .user-message {
        align-items: flex-end;
    }
    
    .user-message .message-bubble {
        background-color: #00A0A0;
        color: white;
        align-self: flex-end;
    }
    
    .ai-message {
        align-items: flex-start;
    }
    
    .ai-message .message-bubble {
        background-color: #f0f0f0;
        color: #333;
        align-self: flex-start;
    }
    
    /* 채팅 내역 햄버거 메뉴 */
    .chat-menu-btn {
        position: absolute;
        top: 20px;
        right: 65px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #00a0a0;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        font-size: 18px;
        z-index: 105;
    }
    
    .chat-menu-btn:hover {
        background-color: #008b8b;
    }
    
    /* 햄버거 메뉴 스타일 */
    .chat-menu {
        position: absolute;
        top: 70px;
        right: 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 160, 160, 0.15);
        z-index: 102;
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 0;
        transition: all 0.3s ease;
        max-height: 0;
        width: 180px;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
    }
    
    .chat-menu.open {
        max-height: 400px;
        opacity: 1;
        pointer-events: all;
        padding: 15px;
    }
    
    .chat-menu-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #333;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 8px;
        transition: all 0.2s;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
    }
    
    .chat-menu-item:hover {
        background-color: #e0f7fa;
    }
    
    .chat-menu-item i {
        font-size: 16px;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: #00a0a0;
        color: white;
    }

    /* 토글 그룹 스타일 */
    .toggle-group {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 30px;
        padding: 5px;
        background: rgba(0, 160, 160, 0.1);
        border-radius: 15px;
        width: fit-content;
        margin: 0 auto 30px;
    }

    .toggle-btn {
        padding: 12px 24px;
        border: none;
        background: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        color: #666;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .toggle-btn:hover {
        color: #00a0a0;
        background: rgba(255, 255, 255, 0.5);
    }

    .toggle-btn.active {
        background: #00a0a0;
        color: white;
        box-shadow: 0 2px 8px rgba(0, 160, 160, 0.2);
    }

    /* 모달 스타일 */
    .modal-content {
        border-radius: 8px;
        border: none;
    }

    .modal-header {
        padding: 15px 20px;
        border-bottom: 1px solid #dee2e6;
    }

    .modal-title {
        font-size: 1.8rem;
        font-weight: 600;
        color: #333;
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

    .session-title {
        margin: 10px 0;
    }

    .session-title h4 {
        font-size: 1.6rem;
        font-weight: 500;
        color: #333;
        margin: 0;
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
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-view:hover {
        background-color: #1ba7b4;
        color: #fff;
    }

    /* 모달 닫기 버튼 스타일 */
    .modal .close {
        font-size: 2rem;
        color: #666;
        opacity: 0.5;
        transition: opacity 0.2s;
    }

    .modal .close:hover {
        opacity: 1;
    }

    /* 모달 내용 영역 스타일 */
    .modal-body {
        padding: 20px;
        max-height: 70vh;
        overflow-y: auto;
    }

    /* 변수 입력 폼 추가 스타일 */
    .input-tooltip {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #00a0a0;
        cursor: help;
    }

    .input-tooltip i {
        font-size: 16px;
    }

    /* 파일 드롭 오버레이 스타일 */
    #drop-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 160, 160, 0.8);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .drop-message {
        color: white;
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        padding: 20px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        backdrop-filter: blur(5px);
    }

    /* 선택된 카테고리 스타일 */
    #ai-category span.selected {
        background: #00a0a0;
        color: white;
    }

    /* 파일 입력 필드 스타일 */
    input[type="file"] {
        padding: 10px;
    }

    /* 텍스트영역 스타일 */
    textarea.form-input {
        resize: vertical;
        min-height: 100px;
    }

    /* 메뉴 구분선 스타일 */
    .menu-divider {
        height: 1px;
        background-color: rgba(0, 160, 160, 0.1);
        margin: 10px 15px;
    }
</style>

<!-- 기존 JavaScript 코드 유지 -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const menuBtn = document.getElementById('menuToggleBtn');
    const menu = document.getElementById('secondaryMenu');
    let isOpen = false;

    menuBtn.addEventListener('click', function() {
        isOpen = !isOpen;
        menu.classList.toggle('open', isOpen);
        menuBtn.innerHTML = isOpen ? '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
    });

    // 메뉴 외부 클릭 시 닫기
    document.addEventListener('click', function(e) {
        if (!menu.contains(e.target) && !menuBtn.contains(e.target) && isOpen) {
            isOpen = false;
            menu.classList.remove('open');
            menuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        }
    });
    });
</script>

<!-- 변수 입력 폼 추가 스타일 -->
<style>
/* 변수 입력 폼 추가 스타일 */
.input-tooltip {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #00a0a0;
    cursor: help;
}

.input-tooltip i {
    font-size: 16px;
}

/* 파일 드롭 오버레이 스타일 */
#drop-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 160, 160, 0.8);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.drop-message {
    color: white;
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    padding: 20px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    backdrop-filter: blur(5px);
}

/* 선택된 카테고리 스타일 */
#ai-category span.selected {
    background: #00a0a0;
    color: white;
}

/* 파일 입력 필드 스타일 */
input[type="file"] {
    padding: 10px;
}

/* 텍스트영역 스타일 */
textarea.form-input {
    resize: vertical;
    min-height: 100px;
}
</style>

<!-- 변수 입력 폼 추가 스타일 -->
<script>
// 변수 데이터 초기화
const variables = <?= json_encode($variables ?? []) ?>;
const subCategories = <?= json_encode($subCategories ?? []) ?>;

// 변수 폼 생성 함수
function createVariableFields(categoryId) {
    const container = document.getElementById('variables-container');
    container.innerHTML = ''; // 컨테이너 초기화

    // PHP에서 이미 가져온 변수들을 사용
    variables.forEach(variable => {
        const formRow = document.createElement('div');
        formRow.className = `form-row ${variable.cv_name === '기타 요구사항' ? 'optional' : ''}`;

        // 라벨 생성
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = variable.cv_name;
        
        // 입력 필드 생성
        let input;
        switch(variable.cv_type) {
            case 'text':
                input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-input';
                input.name = `var_${variable.cv_idx}`;
                input.placeholder = variable.cv_description || `${variable.cv_name} 입력`;
                break;

            case 'textarea':
                input = document.createElement('textarea');
                input.className = 'form-input';
                input.name = `var_${variable.cv_idx}`;
                input.placeholder = variable.cv_description || `${variable.cv_name} 입력`;
                input.rows = 4;
                break;

            case 'select':
                input = document.createElement('select');
                input.className = 'form-input';
                input.name = `var_${variable.cv_idx}`;
                const options = JSON.parse(variable.cv_options || '[]');
                options.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option;
                    optionElement.textContent = option;
                    input.appendChild(optionElement);
                });
                break;

            case 'date':
                input = document.createElement('input');
                input.type = 'date';
                input.className = 'form-input';
                input.name = `var_${variable.cv_idx}`;
                break;

            case 'file':
                input = document.createElement('input');
                input.type = 'file';
                input.className = 'form-input';
                input.name = `var_${variable.cv_idx}`;
                input.accept = '.txt,.doc,.docx,.pdf';
                break;
        }

        // 필수 필드 표시
        if (variable.cv_required === 'Y') {
            input.required = true;
        }

        // 설명 툴팁 추가
        if (variable.cv_description) {
            const tooltip = document.createElement('div');
            tooltip.className = 'input-tooltip';
            tooltip.innerHTML = `<i class="fas fa-info-circle"></i>`;
            tooltip.title = variable.cv_description;
            formRow.appendChild(tooltip);
        }

        formRow.appendChild(label);
        formRow.appendChild(input);
        container.appendChild(formRow);
    });
}

// 카테고리 변경 이벤트 처리
document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // 선택된 카테고리 스타일 변경
        document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // 카테고리 ID 업데이트
        const categoryId = this.dataset.categoryId;
        document.querySelector('input[name="ct_idx"]').value = categoryId;

        // AJAX로 새로운 변수 데이터 가져오기
        fetch(`get_category_variables.php?ct_idx=${categoryId}`)
            .then(response => response.json())
            .then(newVariables => {
                // 전역 변수 업데이트
                variables.length = 0;
                variables.push(...newVariables);
                // 변수 필드 업데이트
                createVariableFields(categoryId);
            })
            .catch(error => console.error('변수 로딩 중 오류:', error));
    });
});

// 초기 변수 필드 생성
if (variables.length > 0) {
    createVariableFields(subCategories[0].ct_idx);
}

// 파일 드래그 앤 드롭 처리
const dropOverlay = document.getElementById('drop-overlay');
const form = document.getElementById('variable-form');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    form.addEventListener(eventName, preventDefaults, false);
    document.body.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    form.addEventListener(eventName, () => {
        dropOverlay.style.display = 'flex';
    }, false);
});

['dragleave', 'drop'].forEach(eventName => {
    form.addEventListener(eventName, () => {
        dropOverlay.style.display = 'none';
    }, false);
});

form.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;

    // 파일 입력 필드 찾기
    const fileInputs = form.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        if (files.length > 0) {
            input.files = files;
            // 파일 선택 이벤트 발생
            const event = new Event('change', { bubbles: true });
            input.dispatchEvent(event);
        }
    });
}

// 폼 제출 처리
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('variable-form');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const categoryId = document.querySelector('input[name="ct_idx"]').value;

        // 파일 크기 체크
        const fileInputs = document.querySelectorAll('input[type="file"]');
        const MAX_SIZE = 5 * 1024 * 1024; // 5MB
        const ALLOWED_TYPES = ['.txt', '.doc', '.docx', '.pdf'];

        for (const input of fileInputs) {
            // 파일이 선택되지 않았는지 확인
            if (input.files.length === 0) {
                alert('파일을 첨부해주세요.');
                return;
            }

            // 파일 크기 체크
            const total = Array.from(input.files)
                .reduce((sum, file) => sum + file.size, 0);

            if (total > MAX_SIZE) {
                alert('파일 합계가 5MB를 초과하여 업로드할 수 없습니다.');
                return;
            }

            // 파일 형식 체크
            const invalidFiles = Array.from(input.files)
                .filter(file => !ALLOWED_TYPES.some(type => 
                    file.name.toLowerCase().endsWith(type)
                ));
            
            if (invalidFiles.length > 0) {
                alert('허용된 파일 형식만 업로드 가능합니다. (.txt, .doc, .docx, .pdf)');
                return;
            }
        }

        // API 호출
        $.ajax({
            url: 'process_variables.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#splinner_modal').modal('show');
            },
            success: function(response) {
                $('#splinner_modal').modal('hide');
                if (response.success) {
                    jalert(response.message, function() {
                        location.href = `work_automation_ai_result.php?session_id=${response.session_id}&ct_idx=${categoryId}`;
                    });
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
                console.error('Ajax 오류:', error);
                jalert('서버 오류가 발생했습니다.');
            }
        });
    });
});

// 이전 대화 모달 표시
function showHistory() {
    $('#historyModal').modal('show');
}

// 세션 상세 보기
function viewSession(sessionId) {
    const categoryId = <?= (int)$categoryId ?>;
    location.href = `work_automation_ai_result.php?session_id=${sessionId}&ct_idx=${categoryId}`;
}
</script>

<?php

include $_SERVER['DOCUMENT_ROOT'] . "/foot.inc.php";

include $_SERVER['DOCUMENT_ROOT'] . "/tail.inc.php";

?>