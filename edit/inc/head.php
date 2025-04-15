<?php
if ($_SUB_HEAD_TITLE) {
    $_APP_TITLE = APP_TITLE . ' - ' . $_SUB_HEAD_TITLE;
} else {
    $_APP_TITLE = APP_TITLE;
}

if ($_SUB_HEAD_IMAGE) {
    $_OG_IMAGE = $_SUB_HEAD_IMAGE;
} else {
    $_OG_IMAGE = OG_IMAGE . '?v=' . $v_txt;
}

// www 있으면 www 제거하기
$base_URL = "";
if (!preg_match('/www/', $_SERVER['SERVER_NAME']) == true) {
    // www 없을때
} else {
    // www 있을때
    $base_URL = ($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
    $base_URL .= ($_SERVER['SERVER_PORT'] != '80') ? $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] : str_replace("www.", "", $_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'];

    header('Location: ' . $base_URL);
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8" />
    <meta name="Generator" content="<?= APP_AUTHOR ?>" />
    <meta name="Author" content="<?= APP_AUTHOR ?>" />
    <meta name="Keywords" content="<?= KEYWORDS ?>" />
    <meta name="Description" content="<?= DESCRIPTION ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, shrink-to-fit=no" />
    <meta name="apple-mobile-web-app-title" content="<?= $_APP_TITLE ?>" />
    <meta content="telephone=no" name="format-detection" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta property="og:image" content="<?= $_OG_IMAGE ?>" />
    <meta property="og:image:width" content="151" />
    <meta property="og:image:height" content="79" />
    <meta property="og:title" content="<?= $_APP_TITLE ?>" />
    <meta property="og:description" content="<?= DESCRIPTION ?>" />
    <meta property="og:url" content="<?= APP_DOMAIN . $_SERVER['REQUEST_URI'] ?>" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?= CDN_HTTP ?>/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= CDN_HTTP ?>/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= CDN_HTTP ?>/img/favicon-16x16.png">
<!--    <link rel="manifest" href="--><?//= CDN_HTTP ?><!--/img/site.webmanifest">-->
    <link rel="mask-icon" href="<?= CDN_HTTP ?>/img/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#603cba">
    <meta name="theme-color" content="#ffffff">
    <meta name="google-adsense-account" content="ca-pub-1149877771418201">
    <title><?= $_APP_TITLE ?></title>

    <!-- 제이쿼리 -->
    <script src="<?= CDN_HTTP ?>/js/jquery.min.js"></script>

    <!--부트스트랩-->
    <!--
    <link rel="stylesheet" href="<?= CDN_HTTP ?>/css/boot_custom.css">
    <script src="<?= CDN_HTTP ?>/js/bootstrap.bundle.min.js"></script> -->
    <link rel="stylesheet" href="<?= DESIGN_HTTP ?>/css/boot_custom.css">
    <script src="<?= DESIGN_HTTP ?>/js/bootstrap.bundle.min.js"></script>
    <!-- 로티 -->
    <script src="<?= CDN_HTTP ?>/js/lottie-player.js"></script>
    <!-- 별점 -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/raty/2.8.0/jquery.raty.min.js"></script>
    <!-- xe아이콘 -->
    <link rel="stylesheet" href="<?= CDN_HTTP ?>/css/xeicon.min.css">

    <!-- ie css 변수적용 -->
    <script src="<?= CDN_HTTP ?>/js/ie11CustomProperties.min.js"></script>

    <!-- 폰트-->
    <link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.8/dist/web/variable/pretendardvariable.css" />

    <!-- Swiper -->
    <link rel="stylesheet" href="<?= CDN_HTTP ?>/css/swiper-bundle.min.css" />
    <script src="<?= CDN_HTTP ?>/js/swiper-bundle.min.js"></script>

    <!-- JS -->
    <script src="<?= CDN_HTTP ?>/js/custom.js?v=<?= $v_txt ?>"></script>

    <!-- CSS -->
    <!--     <link rel="stylesheet" href="<?= CDN_HTTP ?>/css/custom.css">
    <link rel="stylesheet" href="<?= CDN_HTTP ?>/css/design_jh.css">
    <link rel="stylesheet" href="<?= CDN_HTTP ?>/css/design.css"> -->
    <link rel="stylesheet" href="<?= DESIGN_HTTP ?>/css/custom.css?v=<?= $v_txt ?>">
    <link rel="stylesheet" href="<?= DESIGN_HTTP ?>/css/design.css?v=<?= $v_txt ?>">
    <link rel="stylesheet" href="<?= CDN_HTTP ?>/css/flatpickr.min.css">
    <link rel="stylesheet" href="<?= DESIGN_HTTP ?>/css/ai.css?v=<?= $v_txt ?>">

    <!-- DEV -->
    <link rel="stylesheet" href="<?= CDN_HTTP ?>/css/default_dev.css?v=<?= $v_txt ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
    <script src="<?= CDN_HTTP ?>/js/jalert.js?v=<?= $v_txt ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script type="text/javascript">
        <!--
        $.extend($.validator.messages, {
            required: "필수 항목입니다.",
            remote: "항목을 수정하세요.",
            email: "유효하지 않은 E-Mail주소입니다.",
            url: "유효하지 않은 URL입니다.",
            date: "올바른 날짜를 입력하세요.",
            dateISO: "올바른 날짜(ISO)를 입력하세요.",
            number: "유효한 숫자가 아닙니다.",
            digits: "숫자만 입력 가능합니다.",
            creditcard: "신용카드 번호가 바르지 않습니다.",
            equalTo: "같은 값을 다시 입력하세요.",
            extension: "올바른 확장자가 아닙니다.",
            maxlength: $.validator.format("{0}자를 넘을 수 없습니다. "),
            minlength: $.validator.format("{0}자 이상 입력하세요."),
            rangelength: $.validator.format("문자 길이가 {0} 에서 {1} 사이의 값을 입력하세요."),
            range: $.validator.format("{0} 에서 {1} 사이의 값을 입력하세요."),
            max: $.validator.format("{0} 이하의 값을 입력하세요."),
            min: $.validator.format("{0} 이상의 값을 입력하세요."),
        });

        $.validator.setDefaults({
            onkeyup: false,
            onclick: false,
            onfocusout: false,
            showErrors: function(errorMap, errorList) {
                if (this.numberOfInvalids()) { // 에러가 있으면
                    $.alert({
                        title: '',
                        type: 'red',
                        typeAnimated: true,
                        content: errorList[0].message,
                        buttons: {
                            confirm: {
                                btnClass: 'btn-default btn-lg btn-block',
                                text: "확인",
                                action: function() {
                                    errorList[0].element.focus()
                                },
                            },
                        },
                    });
                }
            }
        });
        //
        -->
    </script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js"></script>
    <script type="text/javascript">
        <!--
        moment.locale('ko');
        //
        -->
    </script>

    <script src="<?= CDN_HTTP ?>/js/default_dev.js?v=<?= $v_txt ?>"></script>
    <script src="<?= CDN_HTTP ?>/js/common.js?v=<?= $v_txt ?>"></script>
    <script src="<?= CDN_HTTP ?>/js/flatpickr.min.js"></script>
    <script src="<?= CDN_HTTP ?>/js/flatpickr.ko.js"></script>
</head>

<body>
<div class="wrap">
    <!-- 상단 -->
    <?php if ($_GET['hd_num'] == '1') { ?>
        <div class="hd_m align-items-center justify-content-between">
            <div class="hd_m_top">
                <div class="logo"><img src="./img/logo.svg"></div>
                <div class="hd_btn">
                    <button class="top_alim ml-4" type="button" onclick="location.href='./cart.php'">
                        <img src="./img/cart_m.svg" alt="장바구니">
                        <span></span>
                    </button>
                </div>
            </div>
            <div class="hd_m_mid">
                <form class="sch_ip align-items-center">
                    <input type="search" class="form-control fs_14 flex-fill border-0" placeholder="검색어를 입력해주세요">
                    <button class="btn btn-icon flex-shrink-0"><img src="./img/m_search.svg"></button>
                </form>
            </div>
            <div class="hd_m_bot">
                <ul class="m_top_menu">
                    <li><a href="item_best.php"><p class="fw_500">베스트</p></a></li>
                    <li><a href="item_sale.php"><p class="fw_500">특가</p></a></li>
                    <li><a href="item_new.php"><p class="fw_500">신상품</p></a></li>
                    <li><a href="event_list.php"><p class="fw_500">이벤트</p></a></li>
                </ul>
            </div>
        </div>
        <!-- 모바일 메뉴 영역 -->
        <div class="m_menu_wr">
            <div class="m_nav">
                <? include_once(SITE_PATH."/inc/nav.php"); ?>
            </div>
            <div class="menu_bg"></div>
        </div>
    <?php } else if ($_GET['hd_num'] == '2') { ?>
        <div class="hd_m align-items-center justify-content-between">
            <div class="m-subhead-box">
                <button class="mr_20" type="button" onclick="history.back()"><img src="./img/ic_back.png" alt="뒤로가기"></button>
                <!-- 이전 결과값이 아닌 이전페이지로 이동되어야 합니다. -->
                <div class="page_tit line1_text fs_20 fw_800 flex-fill"><?= $_SUB_HEAD_TITLE ?></div>
            </div>
        </div>

        <!-- 모바일 메뉴 영역 -->
        <div class="m_menu_wr">
            <div class="m_nav">
                <? include_once(SITE_PATH."/inc/nav.php"); ?>
            </div>
            <div class="menu_bg"></div>
        </div>
    <?php } else if ($_GET['hd_num'] == '3') { ?>
        <div class="hd_m align-items-center justify-content-between">
            <div class="hd_btn"><button type="button" onclick="history.back()"><img src="./img/ic_back.png" alt="뒤로가기"></button></div><!-- 이전 결과값이 아닌 이전페이지로 이동되어야 합니다. -->
            <div class="page_tit line1_text fs_16 fw_700 flex-fill text-center"><?= $_SUB_HEAD_TITLE ?></div>
            <div class="hd_btn"></div>
        </div>
    <?php } ?>

    <!-- 바텀 메뉴 -->
    <?php
    if ($_GET['bt_menu'] == '1') {
        include_once(SITE_PATH.'/inc/bt_menu.php');
    } else {
    }
    ?>

    <?php include_once(SITE_PATH."/inc/header.php");?>




