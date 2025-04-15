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
            <div id="ai-create-container">
                <h3 class="fs_40 fw_700">생활기록부</h3>
                <div id="ai-category">
                    <a class="fw_600" href="./student_record_behavioral_development">행발</a>
                    <a class="fw_600" href="./student_record_subject_details">교과세특</a>
                    <span class="fw_600 selected">창체</span>
                </div>
                <div class="ai-variable dropdown">
                    <div class="title"><span>종류</span></div>
                    <div id="kind" class="ai-input-field" type="button" data-toggle="dropdown" aria-expanded="false">
                        <span id="kind_placeholder" class="placeholder">객관식 (동아리 활동, 자율 활동, 진로 활동)</span>
                        <span id="selected_kind"></span>
                        <div class="dropdown-icon"></div>
                    </div>
                    <div class="dropdown-menu">
                        <button id="kind_club" class="dropdown-item" onclick="select_kind_option('club');">동아리 활동</button>
                        <button id="kind_autonomy" class="dropdown-item" onclick="select_kind_option('autonomy');">자율 활동</button>
                        <button id="kind_course" class="dropdown-item" onclick="select_kind_option('course');">진로 활동</button>
                    </div>
                    <input id="kind_value" type="hidden" />
                </div>
                <div class="ai-variable">
                    <div class="title"><span>활동 내용</span></div>
                    <input
                        class="ai-input-field"
                        type="text"
                        placeholder="단답식 (예시: 사진 찍기 활동)" />
                </div>
                <div class="ai-variable">
                    <div class="title"><span>활동 날짜</span></div>
                    <div class="ai-input-field date-picker-wrapper">
                        <input
                            type="text"
                            id="activityDate"
                            placeholder="날짜를 선택해주세요"
                            readonly="readonly" />
                        <div class="dropdown-icon"></div>
                    </div>
                </div>
                <div class="ai-variable">
                    <div class="title"><span>인원 수</span></div>
                    <input
                        class="ai-input-field"
                        type="number"
                        placeholder="단답식"
                        min="1" 
                        step="1"
                        onkeydown="return event.keyCode !== 190"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')" />
                </div>
                <div class="ai-variable dropdown">
                    <div class="title"><span>글자 수</span></div>
                    <div id="text_length" class="ai-input-field" type="button" data-toggle="dropdown" aria-expanded="false">
                        <span id="length_placeholder" class="placeholder">객관식 (600, 700, 1000, 1500 바이트 중 하나 선택)</span>
                        <span id="selected_length"></span>
                        <div class="dropdown-icon"></div>
                    </div>
                    <div class="dropdown-menu">
                        <button id="length_600" class="dropdown-item" onclick="select_length_option('600');">600 바이트</button>
                        <button id="length_700" class="dropdown-item" onclick="select_length_option('700');">700 바이트</button>
                        <button id="length_1000" class="dropdown-item" onclick="select_length_option('1000');">1000 바이트</button>
                        <button id="length_1500" class="dropdown-item" onclick="select_length_option('1500');">1500 바이트</button>
                    </div>
                    <input id="text_length_value" type="hidden" />
                </div>
                <button class="btn-create fw_500">생성하기</button>
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

const kindNames = {
    'club': '동아리 활동',
    'autonomy': '자율 활동',
    'course': '진로 활동'
};
let selectedKind = null;
function select_kind_option(kind) {
    const $kindBtn = $(`#kind_${kind}`);

    if ($kindBtn.hasClass('selected')) {
        return false;
    }

    if (selectedKind !== null) {
        $(`#kind_${selectedKind}`).removeClass('selected');
    }
    $kindBtn.addClass('selected');
    $('#kind_placeholder').hide();
    $('#selected_kind').text(kindNames[kind]);
    $('#kind_value').val(kind);
    selectedKind = kind;

    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    flatpickr("#activityDate", {
        locale: "ko",
        dateFormat: "Y-m-d",
        disableMobile: "true",
        maxDate: "today",
        monthSelectorType: "static",
        placeholder: "날짜를 선택해주세요",
        position: "auto right",
        positionElement: document.getElementById('activityDate'),

        onOpen: function() {
            document.querySelector('.date-picker-wrapper').classList.add('active');
        },
        
        onClose: function() {
            document.querySelector('.date-picker-wrapper').classList.remove('active');
        },

        onChange: function(selectedDates, dateStr, instance) {
            const input = document.getElementById('activityDate');
            input.setAttribute('value', dateStr);
        }
    });
});

let selectedLength = null;
function select_length_option(length) {
    const $lengthBtn = $(`#length_${length}`);

    if ($lengthBtn.hasClass('selected')) {
        return false;
    }

    if (selectedLength !== null) {
        $(`#length_${selectedLength}`).removeClass('selected');
    }
    $lengthBtn.addClass('selected');
    $('#length_placeholder').hide();
    $('#selected_length').text(length + ' 바이트');
    $('#text_length_value').val(length);
    selectedLength = length;

    return false;
}
</script>

<? include_once("./inc/tail.php"); ?>