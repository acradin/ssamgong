<?
$_SUB_HEAD_TITLE = "생활기록부"; //헤더에 타이틀명이 없을경우 공백
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
            <div id="ai-create-container" class="result-box">
                <h3 class="fs_40 fw_700">생활기록부</h3>
                <div class="history-result-box">
                    <div class="history-box">
                        <span class="fs_16 fw_700 title-text">대화 내역</span>
                        <div class="box-border"></div>
                    </div>
                    <div class="result-box">
                        <span class="fs_16 fw_700 title-text">결과</span>
                        <div class="box-border"></div>
                    </div>
                </div>
                <div class="add-request-box">
                    <span class="fs_16 fw_700 title-text">추가 요청</span>
                    <div class="box-border"></div>
                </div>
                <button class="btn-create result-page fw_500">생성하기</button>
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