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
// 오늘 날짜
$today = date('Y-m-d');
$row = $DB->where('visit_date', $today)->getone('visit_sum_t');

$oneHourAgo = time() - 3600; // 1시간 전의 타임스탬프

if ($row['count'] > 0) {
    // 이미 방문한 기록이 있는 경우
    if (!isset($_COOKIE['visit_check']) || (isset($_COOKIE['visit_time']) && $_COOKIE['visit_time'] < $oneHourAgo)) {
        // 쿠키가 없거나 방문 시간이 1시간 이상 전일 경우 카운트 증가
        unset($arr_query);
        $arr_query = array(
            'count' => $row['count'] + 1,
        );
        $DB->where('visit_date', $today);
        $DB->update('visit_sum_t', $arr_query);

        // 쿠키 설정: 방문 체크 및 시간 기록
        setcookie('visit_check', true, time() + 2592000);
        setcookie('visit_time', time(), time() + 2592000);
        $_SESSION['visit_check'] = true;
    }
} else {
    // 오늘 처음 방문하는 경우, 새로운 레코드 추가
    unset($arr_query);
    $arr_query = array(
        'visit_date' => $today,
        'count' => 1,
    );
    $DB->insert('visit_sum_t', $arr_query);

    // 쿠키 설정
    setcookie('visit_check', true, time() + 2592000);
    setcookie('visit_time', time(), time() + 2592000);
    $_SESSION['visit_check'] = true;
}

// 30일 전 최근 본 자료 삭제
view_list_delete();
ad_check();
retire_del_chk();
$cart_cnt = '0';
if ($_SESSION['_mt_idx']) {
    $member_info = get_member_t_info();
    // 장바구니 상품 개수 조회
    $cart_count = $DB->where('mt_idx', $_SESSION['_mt_idx'])
        ->where('ct_status', '0')
        ->getone('cart_t', 'count(*) as cnt');
    $cart_cnt = $cart_count['cnt'];

    // 카테고리별 조회 비율 계산 후 추천 pt_idx 추출
    $option_data = get_recommended_products($_SESSION['_mt_idx']);
    //    printr($option_data);

    if ($option_data) {
        foreach ($option_data as $option_data_row) {
            // 추천 자료를 업데이트하기 위한 JSON 데이터 생성
            $recommendation_json = json_encode(array(
                'recommend_pt_idx' => $option_data_row['recommend_pt_idx'],
                'recommended_products' => $option_data_row['recommended_products']
            ));

            // 오늘 날짜 가져오기
            $today = date('Y-m-d');

            // 오늘 날짜로 데이터가 있는지 확인
            $existing_data = $DB->where('mt_idx', $_SESSION['_mt_idx'])
                ->where('DATE(ast_wdate)', $today)
                ->getone('ai_search_t');

            // 추천자료 테이블에 데이터 삽입 또는 업데이트
            $update_data = array(
                'ast_json_data' => $recommendation_json,
                'ast_wdate' => date('Y-m-d H:i:s'),
                'ast_udate' => date('Y-m-d H:i:s')
            );

            if ($existing_data) {
                // 추천 자료를 업데이트합니다
                $DB->where('mt_idx', $_SESSION['_mt_idx'])->update('ai_search_t', $update_data);
            } else {
                // 삽입 쿼리로 바꿀 수 있습니다 (업데이트 쿼리 대신)
                $DB->insert('ai_search_t', array_merge($update_data, array('mt_idx' => $_SESSION['_mt_idx'])));
            }
        }
    }
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
    <link rel="manifest" href="<?= CDN_HTTP ?>/img/site.webmanifest">
    <link rel="mask-icon" href="<?= CDN_HTTP ?>/img/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#603cba">
    <meta name="theme-color" content="#ffffff">
    <meta name="google-site-verification" content="qMrmbhQisqhS1rpfnitDXoygzm1Bn-4C6wsDjn9I3N4" />
    <meta name="google-adsense-account" content="ca-pub-1149877771418201">
    <title><?= $_APP_TITLE ?></title>

    <!-- 제이쿼리 -->
    <script src="<?= CDN_HTTP ?>/js/jquery.min.js"></script>

    <!--부트스트랩-->
    <link rel="stylesheet" href="<?= DESIGN_HTTP ?>/css/boot_custom.css">
    <script src="<?= DESIGN_HTTP ?>/js/bootstrap.bundle.min.js"></script>
    <!-- 로티 -->
    <script src="<?= CDN_HTTP ?>/js/lottie-player.js"></script>
    <!-- 별점 -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/raty/2.8.0/jquery.raty.min.js"></script>
    <!-- 차트js -->
    <script type="text/javascript" src="<?= DESIGN_HTTP ?>/js/chart.js"></script>

    <!-- xe아이콘 -->
    <link rel="stylesheet" href="<?= CDN_HTTP ?>/css/xeicon.min.css">

    <!-- ie css 변수적용 -->
    <script src="<?= CDN_HTTP ?>/js/ie11CustomProperties.min.js"></script>

    <!-- 폰트-->
    <link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.8/dist/web/variable/pretendardvariable.css" />

    <!-- Swiper -->
    <link rel="stylesheet" href="<?= DESIGN_HTTP ?>/css/swiper-bundle.min.css" />
    <script src="<?= DESIGN_HTTP ?>/js/swiper-bundle.min.js"></script>

    <!-- JS -->
    <script src="<?= DESIGN_HTTP ?>/js/design.js?v=<?= $v_txt ?>"></script>
    <script src="<?= DESIGN_HTTP ?>/js/custom.js?v=<?= $v_txt ?>" defer></script>

    <!--flatpickr-->
    <script src="<?= CDN_HTTP ?>/js/flatpickr.min.js"></script>
    <script src="<?= CDN_HTTP ?>/js/flatpickr.ko.js"></script>
    <link rel="stylesheet" href="<?= CDN_HTTP ?>/css/flatpickr.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= DESIGN_HTTP ?>/css/custom.css?v=<?= $v_txt ?>">
    <link rel="stylesheet" href="<?= DESIGN_HTTP ?>/css/design.css?v=<?= $v_txt ?>">

    <!-- DEV -->
    <link rel="stylesheet" href="<?= CDN_HTTP ?>/css/default_dev.css?v=<?= $v_txt ?>">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ko.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
    <script src="<?= CDN_HTTP ?>/js/jalert.js?v=<?= $v_txt ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

    <?php if ($chk_webeditor == "Y") { ?>
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" />
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-ko-KR.min.js"></script>
        <!--<script type="text/javascript" src="<?= CDN_HTTP ?>/js/summernote-ko-KR.js"></script>-->
    <?php } ?>

    <script type="text/javascript">
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
                        type: '',
                        typeAnimated: true,
                        content: errorList[0].message,
                        buttons: {
                            confirm: {
                                btnClass: 'btn btn-primary btn-block',
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
    </script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js"></script>
    <script type="text/javascript">
        moment.locale('ko');
    </script>
    <script src="<?= CDN_HTTP ?>/js/default_dev.js?v=<?= $v_txt ?>"></script>
</head>
<style>
    @font-face {
        font-family: '본고딕';
        src: url('<?= CDN_HTTP ?>/font/Noto_sans(본고딕)/NotoSansKR-VariableFont_wght.ttf') format('opentype');
    }

    @font-face {
        font-family: '나눔고딕';
        src: url('<?= CDN_HTTP ?>/font/나눔고딕/NanumFontSetup_OTF_GOTHIC/NanumBarunGothic.otf') format('opentype');
    }

    @font-face {
        font-family: '나눔명조';
        src: url('<?= CDN_HTTP ?>/font/나눔명조/NanumFontSetup_OTF_MYUNGJO/NanumMyeongjo.otf') format('opentype');
    }

    @font-face {
        font-family: '나눔바른고딕';
        src: url('<?= CDN_HTTP ?>/font/나눔바른고딕/NanumFontSetup_OTF_BARUNGOTHIC/NanumBarunGothic.otf') format('opentype');
    }

    @font-face {
        font-family: '나눔스퀘어';
        src: url('<?= CDN_HTTP ?>/font/나눔스퀘어/NanumFontSetup_OTF_SQUARE/NanumSquareR.otf') format('opentype');
    }

    @font-face {
        font-family: '학교안심 받아쓰기';
        src: url('<?= CDN_HTTP ?>/font/학교안심 받아쓰기/Hakgyoansim Badasseugi OTF B.otf') format('opentype');
    }

    @font-face {
        font-family: '학교안심 알림장';
        src: url('<?= CDN_HTTP ?>/font/학교안심 알림장/Hakgyoansim Allimjang OTF B.otf') format('opentype');
    }
</style>

<body>
    <div class="wrap">
        <!-- 상단 -->
        <?php
        if ($_GET['hd_num'] == '1') {
        ?>
            <div class="hd_m align-items-center justify-content-between">
                <div class="hd_m_top">
                    <div class="logo" onclick="location.href='./'"><img src="<?= CDN_HTTP ?>/img/logo.svg"></div>
                    <div class="hd_m_mid">
                        <form class="sch_ip align-items-center">
                            <input type="search" id="searchinput2" class="form-control fs_14 flex-fill border-0" placeholder="검색어를 입력해주세요" onclick="f_search_click()">
                            <button class="btn btn-icon flex-shrink-0"><img src="<?= CDN_HTTP ?>/img/m_search.svg"></button>
                        </form>
                    </div>
                    <div class="hd_btn">
                        <?php
                        //읽지 않은 알림이 있는지?
                        if ($_SESSION['_mt_idx']) {
                            $DB->where('mt_idx', $_SESSION['_mt_idx']);
                            $DB->where('plt_read_chk', 'N');
                            $DB->where('plt_show', 'Y');
                            $row_alarm = $DB->getone('push_log_t', 'count(*) as cnt');

                            if ($row_alarm['cnt'] > 0) {
                                $alarm_t = ' top_alim';
                            } else {
                                $alarm_t = '';
                            }
                        ?>
                            <button class="<?= $alarm_t ?>" type="button" onclick="location.href='./alarm'">
                                <img src="./img/ic_noti.svg" alt="알림">
                                <span></span>
                            </button>
                        <?php } ?>
                        <button class="top_alim ml_10" type="button" onclick="location.href='./cart'">
                            <img src="./img/cart.svg" alt="장바구니">
                            <span class="cart_num"><?= $cart_cnt > 99 ? '99+' : $cart_cnt ?></span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- 모바일 메뉴 영역 -->
            <div class="m_menu_wr">
                <div class="m_nav">
                    <?php include("./inc/nav.php"); ?>
                </div>
                <div class="menu_bg"></div>
            </div>
        <?php } else if ($_GET['hd_num'] == '2') {
            if (!$_GET['hd_url']) {
                $hd_url = 'history.back()';
            } else {
                $hd_url = "location.replace('" . $_GET['hd_url'] . "');";
            }
        ?>
            <div class="hd_m align-items-center justify-content-between">
                <div class="m-subhead-box">
                    <button class="mr_20" type="button" onclick="<?= $hd_url ?>"><img src="<?= CDN_HTTP ?>/img/ic_back.png" alt="뒤로가기"></button>
                    <!-- 이전 결과값이 아닌 이전페이지로 이동되어야 합니다. -->
                    <div class="page_tit line1_text fs_20 fw_800 flex-fill"><?= $_SUB_HEAD_TITLE ?></div>
                </div>
            </div>
        <?php } else if ($_GET['hd_num'] == '3') { ?>
            <div class="hd_m">
                <div class="flex-bw text-align-center py_15 px_16">
                    <button type="button" onclick="location.replace('./')"><img src="./img/ic_back.svg" alt="뒤로가기"></button>
                    <!-- 이전 결과값이 아닌 이전페이지로 이동되어야 합니다. -->
                    <div class="page_tit line1_text fs_18 fw_800 flex-fill"><?= $_SUB_HEAD_TITLE ?></div>
                    <button class="top_alim" type="button" onclick="location.href='./cart'">
                        <img src="./img/cart.svg" alt="장바구니">
                        <span class="cart_num"><?= $cart_cnt > 99 ? '99+' : $cart_cnt ?></span>
                    </button>
                </div>
            </div>
        <?php } else if ($_GET['hd_num'] == '4') { ?>
            <div class="hd_m">
                <div class="flex-bw text-align-center py_15 px_16">
                    <button type="button" onclick="history.back()"><img src="./img/ic_back.svg" alt="뒤로가기"></button>
                    <!-- 이전 결과값이 아닌 이전페이지로 이동되어야 합니다. -->
                    <div class="page_tit line1_text fs_18 fw_800 flex-fill"><?= $_SUB_HEAD_TITLE ?></div>
                    <button type="button" onclick="location.href='./'">
                        <img src="./img/ic_home.svg" alt="홈">
                    </button>
                </div>
            </div>
        <?php } else if ($_GET['hd_num'] == '5') { ?>
            <div class="hd_m">
                <div class="flex-bw py_15 px_16">
                    <!-- 이전 결과값이 아닌 이전페이지로 이동되어야 합니다. -->
                    <div class="page_tit line1_text fs_18 fw_800 flex-fill"><?= $_SUB_HEAD_TITLE ?></div>
                    <button class="m_delete"><img src="./img/delete.svg" style="width:18px"></button>
                </div>
            </div>
        <?php } else if ($_GET['hd_num'] == '6') { ?>
            <div class="hd_m">
                <div class="flex-bw text-align-center py_15 px_16">
                    <div class="flex-c">
                        <button type="button" onclick="history.back()" class="mr_10"><img src="./img/ic_back.svg" alt="뒤로가기" style="min-width:2.5rem;"></button>
                        <button type="button" onclick="location.href='./'"><img src="./img/ic_home.svg" alt="홈" style="min-width:2.8rem;"></button>
                    </div>
                    <div class="page_tit line1_text fs_18 fw_800 flex-fill mx_10"><?= $_SUB_HEAD_TITLE ?></div>
                    <div class="flex-c">
                        <button class="top_alim mr_10" type="button" onclick="location.href='./cart'">
                            <img src="./img/cart.svg" alt="장바구니" style="min-width:2.8rem;">
                            <span class="cart_num"><?= $cart_cnt > 99 ? '99+' : $cart_cnt ?></span>
                        </button>
                        <button class="btn btn-icon flex-shrink-0"><img src="./img/ic_top_sch.svg" style="min-width:2.8rem;"></button>
                    </div>
                </div>
            </div>
        <?php } else if ($_GET['hd_num'] == '7') { ?>
            <div class="hd_m">
                <div class="flex-bw text-align-center py_15 px_16">
                    <div class="flex-c">
                        <button type="button" onclick="history.back()" class="mr_10"><img src="./img/ic_back.svg" alt="뒤로가기" style="min-width:2.5rem;"></button>
                    </div>
                    <div class="page_tit line1_text fs_18 fw_800 flex-fill mx_10"><?= $_SUB_HEAD_TITLE ?></div>
                    <?php
                    //읽지 않은 알림이 있는지?
                    if ($_SESSION['_mt_idx']) {
                        $DB->where('mt_idx', $_SESSION['_mt_idx']);
                        $DB->where('plt_read_chk', 'N');
                        $DB->where('plt_show', 'Y');
                        $row_alarm = $DB->getone('push_log_t', 'count(*) as cnt');
                        if ($row_alarm['cnt'] > 0) {
                            $alarm_t = ' top_alim';
                        } else {
                            $alarm_t = '';
                        }
                    ?>
                        <button class="<?= $alarm_t ?>" type="button" onclick="location.href='./alarm_setting'">
                            <img src="./img/ic_noti2.svg" alt="알림설정">
                        </button>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <!-- 바텀 메뉴 -->
        <?php
        if ($_GET['bt_menu'] == '1') {
            include_once('./inc/bt_menu.php');
        }
        ?>
        <?php include_once("./inc/header.php"); ?>