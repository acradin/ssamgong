<?
$_SUB_HEAD_TITLE = "가정통신문"; //헤더에 타이틀명이 없을경우 공백
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
								<img src="./img/test-subbanner.jpg">
							</a>
						</li>
						<li class="swiper-slide">							
							<a href="">	<img src="./img/test-subbanner.jpg"></a>					
						</li>
					</ul>
				</div>
				<div class="swiper-button-prev banner-prev"><img src="./img/prev.svg"></div>
				<div class="swiper-button-next banner-next"><img src="./img/next.svg"></div>
			</div>
            <!-- AI 리스트 -->
            <div class="item_box">
                <div id="ai-create-container">
                    <h3 class="fs_40 fw_700">생활기록부</h3>
                    <div class="ai-variable dropdown">
                        <div class="title"><span>종류</span></div>
                        <div id="kind" class="ai-input-field" type="button" data-toggle="dropdown" aria-expanded="false">
                            <span id="kind_placeholder" class="placeholder">객관식 (신학기 편지, 성적통지표(전체), 성적통지표(개인), 학부모 문자)</span>
                            <span id="selected_kind"></span>
                            <div class="dropdown-icon"></div>
                        </div>
                        <div class="dropdown-menu">
                            <button id="kind_new_year" class="dropdown-item" onclick="select_kind_option('new_year');">신학기 편지</button>
                            <button id="kind_total" class="dropdown-item" onclick="select_kind_option('total');">성적통지표(전체)</button>
                            <button id="kind_individual" class="dropdown-item" onclick="select_kind_option('individual');">성적통지표(개인)</button>
                            <button id="kind_parent" class="dropdown-item" onclick="select_kind_option('parent');">학부모 문자</button>
                        </div>
                        <input id="kind_value" type="hidden" />
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
                    <div class="ai-variable">
                        <div class="title"><span>필수 전달 내용</span></div>
                        <input
                            class="ai-input-field"
                            type="text"
                            placeholder="단답식" />
                    </div>
                    <button class="btn-create fw_500">생성하기</button>
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




const kindNames = {
    'new_year': '신학기 편지',
    'total': '성적통지표(전체)',
    'individual': '성적통지표(개인)',
    'parent': '학부모 문자'
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