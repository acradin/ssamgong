<?
$_SUB_HEAD_TITLE = "업무자동화 AI"; //헤더에 타이틀명이 없을경우 공백
$_GET['hd_pc'] = '1';//PC hd 메뉴있음1, 메뉴없음 공백
$_GET['hd_num'] = '1';//모바일 hd 1~n까지 있음
$_GET['bt_menu'] = '1'; //모바일 하단메뉴 있음1, 없음 공백
include_once("./inc/head.php");
?>

<div class="wrap">
    <div class="sub_pg">
        <div class="container">
            <!-- 상단 서브배너 -->
            <div class="sub-top-banner relative">
                <div class="swiper">
                    <ul class="swiper-wrapper">
                        <li class="swiper-slide">
                            <a href="">
                                <img src="./img/ai-banner.jpg">
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- AI 리스트 -->
            <div class="data-box">
                <div class="board-top">
                    <h3 class="fs_30 fw_700">업무 자동화 AI</h3>
                </div>
                <div id="ai-container">
                    <ul>
                        <li>
                            <span class="ai-name">생활기록부</span>
                            <div class="ai-lore"><p class="fw_500">생기부 작성을 자동화 시켜주는 AI입니다.</p></div>
                            <a class="btn-ai-link fw_500" href="./student_record_behavioral_development">바로가기</a>
                        </li>
                        <li>
                            <span class="ai-name">문제 제작</span>
                            <div class="ai-lore"><p class="fw_500">문제(지필평가, 형성평가 등)를 만들어주는 AI입니다.</p></div>
                            <a class="btn-ai-link fw_500" href="./question_written_test">바로가기</a>
                        </li>
                        <li>
                            <span class="ai-name">가정통신문</span>
                            <div class="ai-lore"><p class="fw_500">가정통신문이나 학생 편지를 작성해주는 AI입니다.</p></div>
                            <a class="btn-ai-link fw_500" href="./parent_letter">바로가기</a>
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <span class="ai-name">활동지 제작</span>
                            <div class="ai-lore"><p class="fw_500">각종 활동지 디자인을 뽑아주는 AI입니다.</p></div>
                            <a class="btn-ai-link fw_500">바로가기</a>
                        </li>
                        <li>
                            <span class="ai-name">업무 비서</span>
                            <div class="ai-lore"><p class="fw_500">파일을 넣으면 업무에 대한 상세한 설명을 도와주는 비서 AI입니다.</p></div>
                            <a class="btn-ai-link fw_500">바로가기</a>
                        </li>
                        <li>
                            <span class="ai-name">블로그 작성</span>
                            <div class="ai-lore"><p class="fw_500">블로그 초안을 작성해주는 AI입니다.</p></div>
                            <a class="btn-ai-link fw_500">바로가기</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var swiper = new Swiper(".sub-top-banner .swiper", {
    slidesPerView: 1,
    loop: true,
    navigation: {
        nextEl: ".banner-next",
        prevEl: ".banner-prev",
    },
});
</script>

<? include_once("./inc/tail.php"); ?>