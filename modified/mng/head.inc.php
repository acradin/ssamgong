<?php
include $_SERVER['DOCUMENT_ROOT'] . "/lib.inc.php";

$_APP_TITLE = APP_TITLE;
$_OG_IMAGE = OG_IMAGE;
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
    <!--    <link rel="manifest" href="--><? //= CDN_HTTP 
                                            ?><!--/img/site.webmanifest">-->
    <link rel="mask-icon" href="<?= CDN_HTTP ?>/img/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#603cba">
    <meta name="theme-color" content="#ffffff">
    <title><?= $_APP_TITLE ?></title>

    <!-- base css&js -->
    <link href="<?=CDN_HTTP?>/css/base_mng.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="<?= CDN_HTTP ?>/css/flatpickr.min.css" />
<!--    <link rel="stylesheet" type="text/css" href="--><?php //= CDN_HTTP ?><!--/css/base_mng.css" />-->
    <script type="text/javascript" src="<?= CDN_HTTP ?>/js/base_mng.js"></script>
    <script type="text/javascript" src="<?= CDN_HTTP ?>/js/flatpickr.min.js"></script>
    <!-- icons -->
    <link rel="stylesheet" href="//cdn.materialdesignicons.com/4.7.95/css/materialdesignicons.min.css">

    <!-- fonts -->
    <link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.8/dist/web/static/pretendard.css" />

    <!-- jquery.validate & jalert -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
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
                        content: errorList[0].message,
                        buttons: {
                            confirm: {
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

    <link rel="stylesheet" type="text/css" href="<?= CDN_HTTP ?>/lib/datepicker/jquery.datetimepicker.min.css" />
    <script src="<?= CDN_HTTP ?>/lib/datepicker/jquery.datetimepicker.full.min.js"></script>
    <script type="text/javascript">
        <!--
        jQuery.datetimepicker.setLocale('ko');
        //
        -->
    </script>

    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" integrity="sha512-d9xgZrVZpmmQlfonhQUvTR7lMPtO7NkZMkA0ABN3PHCbKA5nqylQ/yWlFAyY6hYgdF1Qh6nYiuADWwKB4C2WSw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!--    <link rel="stylesheet" type="text/css" href="--><?php //=CDN_HTTP?><!--/css/default.css?v=--><?php //=$v_txt?><!--" />-->
    <link rel="stylesheet" type="text/css" href="<?=CDN_HTTP?>/css/default_mng.css?v=<?=$v_txt?>" />
    <script type="text/javascript" src="<?= CDN_HTTP ?>/js/default_mng.js?v=<?= $v_txt ?>"></script>

    <!-- xe아이콘 -->
    <link rel="stylesheet" href="<?= CDN_HTTP ?>/css/xeicon.min.css">

    <script src="<?= JS_URL ?>/common.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ko.js"></script>
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

    <!-- 토스트 Toast -->
    <div id="common_toast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000">
        <div class="toast-body">
            <p><span id="common_toast_text">아이디 or 비밀번호를 다시 확인해주세요!</span></p>
        </div>
    </div>
    <script>
        function common_toast(text) {
            $('#common_toast_text').text(text);
            const toastToast = document.getElementById('common_toast');
            const toast_confirm = new bootstrap.Toast(toastToast);
            toast_confirm.show();
        }
    </script>

    <script type="text/javascript">
        flatpickr.setDefaults({
            locale: 'ko',
            dateFormat: 'Y-m-d',
            disableMobile: true
        });
    </script>
</body>
</html>