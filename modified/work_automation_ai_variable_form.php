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

<?php
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
    SELECT ct_idx, ct_name
    FROM category_t
    WHERE parent_idx = ? AND ct_status = 'Y'
    ORDER BY ct_order",
    [$categoryId]
);

// 첫 번째 하위 카테고리의 변수 정보 조회 (기본값)
if (!empty($subCategories)) {
    $defaultSubCategory = $subCategories[0];
    $variables = $DB->rawQuery("
        SELECT cv_idx, cv_name, cv_type, cv_description, cv_options
        FROM chatbot_variable_t
        WHERE ct_idx = ? AND cv_status = 'Y'
        ORDER BY cv_order",
        [$defaultSubCategory['ct_idx']]
    );
}
?>

<div id="ai-create-container">
    <h3 class="fs_40 fw_700 mt_20"><?= htmlspecialchars($category['ct_name']) ?></h3>
    
    <!-- 하위 카테고리 탭 -->
    <?php if (count($subCategories) > 1): ?>
        <div id="ai-category">
            <?php foreach ($subCategories as $subCategory): ?>
                <span class="fw_600 <?= ($subCategory['ct_idx'] === $defaultSubCategory['ct_idx']) ? 'selected' : '' ?>" 
                    data-category-id="<?= $subCategory['ct_idx'] ?>">
                    <?= htmlspecialchars($subCategory['ct_name']) ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form id="variable-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="ct_idx" value="<?= $defaultSubCategory['ct_idx'] ?>">
        
        <?php foreach ($variables as $variable): ?>
            <div class="ai-variable <?= $variable['cv_type'] === 'select' ? 'dropdown' : '' ?>">
                <div class="title">
                    <span><?= htmlspecialchars($variable['cv_name']) ?></span>
                </div>
                
                <?php if ($variable['cv_type'] === 'text'): ?>
                    <input
                        class="ai-input-field"
                        type="text"
                        name="var_<?= $variable['cv_idx'] ?>"
                        placeholder="<?= htmlspecialchars($variable['cv_description']) ?>"
                        required />
                        
                <?php elseif ($variable['cv_type'] === 'select'): ?>
                    <?php
                    $options = json_decode($variable['cv_options'], true);
                    $varId = "var_" . $variable['cv_idx'];
                    ?>
                    <div id="<?= $varId ?>_wrapper" class="ai-input-field" type="button" data-toggle="dropdown" aria-expanded="false">
                        <span id="<?= $varId ?>_placeholder" class="placeholder"><?= htmlspecialchars($variable['cv_description']) ?></span>
                        <span id="<?= $varId ?>_selected"></span>
                        <div class="dropdown-icon"></div>
                    </div>
                    <div class="dropdown-menu">
                        <?php foreach ($options as $idx => $option): ?>
                            <button 
                                id="<?= $varId ?>_<?= $idx ?>" 
                                class="dropdown-item" 
                                type="button"
                                onclick="selectOption('<?= $varId ?>', '<?= $option ?>', <?= $idx ?>);">
                                <?= htmlspecialchars($option) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <input id="<?= $varId ?>" name="<?= $varId ?>" type="hidden" required />
                    
                <?php elseif ($variable['cv_type'] === 'date'): ?>
                    <div class="ai-input-field date-picker-wrapper">
                        <input
                            type="text"
                            id="var_<?= $variable['cv_idx'] ?>"
                            name="var_<?= $variable['cv_idx'] ?>"
                            class="date-picker"
                            placeholder="날짜를 선택해주세요"
                            readonly="readonly"
                            required />
                        <div class="dropdown-icon"></div>
                    </div>
                    
                <?php elseif ($variable['cv_type'] === 'file'): ?>
                    <div class="ai-input-field file-input-wrapper">
                        <input 
                            type="file" 
                            id="var_<?= $variable['cv_idx'] ?>"
                            name="var_<?= $variable['cv_idx'] ?>"
                            accept=".pdf,.doc,.docx"
                            required />
                        <span class="file-placeholder">파일을 첨부해주세요</span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <button type="submit" class="btn-create fw_500">생성하기</button>
    </form>
</div>
                

            </div>

        </div>

    </div>

<script>
// 하위 카테고리 클릭 시 변수 로드
$('#ai-category span').click(function() {
    const categoryId = $(this).data('category-id');
    
    // 선택 상태 변경
    $('#ai-category span').removeClass('selected');
    $(this).addClass('selected');
    
    // Ajax로 해당 카테고리의 변수 정보 가져오기
    $.ajax({
        url: 'get_category_variables.php',
        type: 'GET',
        data: { ct_idx: categoryId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // 폼 내용 업데이트
                $('input[name="ct_idx"]').val(categoryId);
                
                // 기존 변수 필드들 제거
                $('#variable-form .ai-variable').remove();
                
                // 새로운 변수 필드들 추가
                const variablesHtml = response.variables.map(variable => {
                    let fieldHtml = `
                        <div class="ai-variable ${variable.cv_type === 'select' ? 'dropdown' : ''}">
                            <div class="title">
                                <span>${variable.cv_name}</span>
                            </div>`;
                    
                    if (variable.cv_type === 'text') {
                        fieldHtml += `
                            <input
                                class="ai-input-field"
                                type="text"
                                name="var_${variable.cv_idx}"
                                placeholder="${variable.cv_description}"
                                required />`;
                    }
                    else if (variable.cv_type === 'select') {
                        const options = JSON.parse(variable.cv_options);
                        const varId = `var_${variable.cv_idx}`;
                        
                        fieldHtml += `
                            <div id="${varId}_wrapper" class="ai-input-field" type="button" data-toggle="dropdown" aria-expanded="false">
                                <span id="${varId}_placeholder" class="placeholder">${variable.cv_description}</span>
                                <span id="${varId}_selected"></span>
                                <div class="dropdown-icon"></div>
                            </div>
                            <div class="dropdown-menu">`;
                        
                        options.forEach((option, idx) => {
                            fieldHtml += `
                                <button 
                                    id="${varId}_${idx}" 
                                    class="dropdown-item" 
                                    type="button"
                                    onclick="selectOption('${varId}', '${option}', ${idx});">
                                    ${option}
                                </button>`;
                        });
                        
                        fieldHtml += `
                            </div>
                            <input id="${varId}" name="${varId}" type="hidden" required />`;
                    }
                    else if (variable.cv_type === 'date') {
                        fieldHtml += `
                            <div class="ai-input-field date-picker-wrapper">
                                <input
                                    type="text"
                                    id="var_${variable.cv_idx}"
                                    name="var_${variable.cv_idx}"
                                    class="date-picker"
                                    placeholder="날짜를 선택해주세요"
                                    readonly="readonly"
                                    required />
                                <div class="dropdown-icon"></div>
                            </div>`;
                    }
                    else if (variable.cv_type === 'file') {
                        fieldHtml += `
                            <div class="ai-input-field file-input-wrapper">
                                <input 
                                    type="file" 
                                    id="var_${variable.cv_idx}"
                                    name="var_${variable.cv_idx}"
                                    accept=".pdf,.doc,.docx"
                                    required />
                                <span class="file-placeholder">파일을 첨부해주세요</span>
                            </div>`;
                    }
                    
                    fieldHtml += `</div>`;
                    return fieldHtml;
                }).join('');
                
                // 변수 필드들을 submit 버튼 앞에 삽입
                $(variablesHtml).insertBefore('#variable-form button[type="submit"]');
                
                // date picker 다시 초기화
                flatpickr(".date-picker", {
                    locale: "ko",
                    dateFormat: "Y-m-d",
                    disableMobile: "true",
                    maxDate: "today",
                    monthSelectorType: "static",
                    placeholder: "날짜를 선택해주세요",
                    position: "auto right",
                    
                    onOpen: function(selectedDates, dateStr, instance) {
                        instance.element.closest('.date-picker-wrapper').classList.add('active');
                    },
                    
                    onClose: function(selectedDates, dateStr, instance) {
                        instance.element.closest('.date-picker-wrapper').classList.remove('active');
                    },

                    onChange: function(selectedDates, dateStr, instance) {
                        instance.element.setAttribute('value', dateStr);
                    }
                });
                
                // 파일 입력 이벤트 다시 바인딩
                document.querySelectorAll('input[type="file"]').forEach(function(input) {
                    input.addEventListener('change', function() {
                        const placeholder = this.parentElement.querySelector('.file-placeholder');
                        if (this.files.length > 0) {
                            placeholder.textContent = this.files[0].name;
                            placeholder.classList.add('has-file');
                        } else {
                            placeholder.textContent = '파일을 첨부해주세요';
                            placeholder.classList.remove('has-file');
                        }
                    });
                });
            }
        }
    });
});

// 선택형 입력 처리
function selectOption(varId, value, idx) {
    const $btn = $(`#${varId}_${idx}`);
    
    if ($btn.hasClass('selected')) {
        return false;
    }
    
    // 이전 선택 제거
    $(`.dropdown-item[id^="${varId}_"]`).removeClass('selected');
    
    // 새로운 선택 적용
    $btn.addClass('selected');
    $(`#${varId}_placeholder`).hide();
    $(`#${varId}_selected`).text(value);
    $(`#${varId}`).val(value);
    
    return false;
}

// 날짜 선택 초기화
document.addEventListener('DOMContentLoaded', function() {
    flatpickr(".date-picker", {
        locale: "ko",
        dateFormat: "Y-m-d",
        disableMobile: "true",
        maxDate: "today",
        monthSelectorType: "static",
        placeholder: "날짜를 선택해주세요",
        position: "auto right",
        
        onOpen: function(selectedDates, dateStr, instance) {
            instance.element.closest('.date-picker-wrapper').classList.add('active');
        },
        
        onClose: function(selectedDates, dateStr, instance) {
            instance.element.closest('.date-picker-wrapper').classList.remove('active');
        },

        onChange: function(selectedDates, dateStr, instance) {
            instance.element.setAttribute('value', dateStr);
        }
    });
});

// 파일 입력 처리
document.querySelectorAll('input[type="file"]').forEach(function(input) {
    input.addEventListener('change', function() {
        const placeholder = this.parentElement.querySelector('.file-placeholder');
        if (this.files.length > 0) {
            placeholder.textContent = this.files[0].name;
            placeholder.classList.add('has-file');
        } else {
            placeholder.textContent = '파일을 첨부해주세요';
            placeholder.classList.remove('has-file');
        }
    });
});

// 폼 제출 처리
$('#variable-form').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
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
                    location.href = `work_automation_ai_result.php?ct_idx=${response.ct_idx}`;
                });
            } else {
                jalert(response.message || '오류가 발생했습니다.');
            }
        },
        error: function(xhr, status, error) {
            $('#splinner_modal').modal('hide');
            console.error('Ajax 오류:', error);
            jalert('서버 오류가 발생했습니다.');
        }
    });
});
</script>

<style>
/* AI 컨테이너 스타일 */
#ai-create-container {
    width: 60%;
    margin: 0 auto;
}

#ai-create-container h3 {
    text-align: center;
    margin-bottom: 3rem;
}

/* 카테고리 스타일 */
#ai-category {
    display: flex;
    align-items: stretch;
    justify-content: center;
    gap: 30px;
    margin-bottom: 2.5rem;
}

#ai-category span {
    width: 15%;
    padding: 0.8rem;
    border-radius: 25px;
    border: 3px solid #1ba7b4;
    text-align: center;
    color: #1ba7b4;
    font-size: 1.8rem;
}

#ai-category span:not(.selected) {
    cursor: pointer;
}

#ai-category .selected {
    color: #fff;
    background-color: #1ba7b4;
}

/* 변수 입력 필드 스타일 */
.ai-variable {
    display: flex;
    border-radius: 10px;
    border: 3px solid #1ba7b4;
    font-size: 1.9rem;
    margin-bottom: 2rem;
}

.ai-variable div.title {
    width: 17%;
    padding: 1.8rem 0;
    border-radius: 5px 0 0 5px;
    background-color: #1ba7b4;
    color: #fff;
    text-align: center;
}

.ai-variable .ai-input-field {
    width: 83%;
    padding: 1.8rem;
    text-align: center;
}

/* 입력 필드 공통 스타일 */
.ai-input-field {
    position: relative;
}

/* 드롭다운 아이콘 스타일 */
.dropdown-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    background-image: url("../img/polygon.svg");
    background-position: center;
    background-repeat: no-repeat;
    background-size: contain;
    transition: transform 0.3s;
}

.ai-input-field[aria-expanded="true"] .dropdown-icon,
.date-picker-wrapper.active .dropdown-icon {
    transform: translateY(-50%) rotate(180deg);
}

.ai-variable .dropdown-menu {
    width: 83%;
}

.dropdown-item.selected {
    background: #F5F6F8;
    color: #44C1CC;
}

/* 파일 입력 스타일 */
.file-input-wrapper {
    position: relative;
    text-align: center;
}

input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

/* 플레이스홀더 스타일 */
.placeholder {
    color: #DBDBDB;
}

.placeholder.has-file {
    color: black;
}

/* 날짜 선택기 스타일 */
.date-picker-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.date-picker-wrapper input {
    width: 100%;
    border: none;
    outline: none;
    text-align: center;
    padding-right: 40px;
}

/* flatpickr 캘린더 스타일 */
.flatpickr-calendar {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    font-size: 14px;
    margin-top: 15px !important;
    transform: translate(0, 10px) !important;
}

.flatpickr-day.selected {
    background: #1ba7b4 !important;
    border-color: #1ba7b4 !important;
}

.flatpickr-day:hover {
    background: #e6f3f5;
}

/* 생성 버튼 스타일 */
.btn-create {
    width: 20%;
    display: block;
    margin: 3.5rem auto 0;
    padding: 1.1rem;
    border-radius: 100px;
    border: 0;
    background-color: #1ba7b4;
    color: #fff;
    font-size: 1.8rem;
    text-align: center;
}

.btn-create:hover {
    background-color:rgb(24, 149, 161);
}

/* 입력 필드 기본 스타일 초기화 */
input {
    border: none;
    outline: none;
    background: none;
    box-shadow: none;
    margin: 0;
    padding: 0;
    font-family: inherit;
    font-size: inherit;
    color: inherit;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

/* 플레이스홀더 스타일 */
::placeholder {
    color: #DBDBDB;
    opacity: 1;
}

:-ms-input-placeholder {
    color: #DBDBDB;
}

::-ms-input-placeholder {
    color: #DBDBDB;
}

.file-placeholder:not(.has-file) {
    color: #DBDBDB;
}
</style>

<?php

include $_SERVER['DOCUMENT_ROOT'] . "/foot.inc.php";

include $_SERVER['DOCUMENT_ROOT'] . "/tail.inc.php";

?>