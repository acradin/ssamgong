<?php
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head.inc.php";
$chk_menu = '13';
$chk_sub_menu = '1';
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head_menu.inc.php";

// 카테고리 ID 체크
if (!isset($_GET['ct_idx'])) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

$categoryId = (int)$_GET['ct_idx'];

// 카테고리 정보 조회
$category = $DB->rawQueryOne("
    SELECT c.*, p.ct_name as parent_name, p.ct_idx as parent_idx
    FROM category_t c
    LEFT JOIN category_t p ON c.parent_idx = p.ct_idx
    WHERE c.ct_idx = ?", 
    [$categoryId]
);

if (!$category) {
    echo "<script>alert('카테고리 정보를 찾을 수 없습니다.'); history.back();</script>";
    exit;
}

// 챗봇 설명 조회
$botDescription = $DB->rawQueryOne("
    SELECT cd_description 
    FROM chatbot_description_t 
    WHERE ct_idx = ? 
    ORDER BY cd_wdate DESC 
    LIMIT 1",
    [$category['parent_idx']]
);

// 프롬프트 정보 조회
$prompt = $DB->rawQueryOne("
    SELECT cp_title, cp_content
    FROM chatbot_prompt_t
    WHERE ct_idx = ?",
    [$categoryId]
);

// 변수 정보 조회 (cv_status 조건 제거)
$variables = $DB->rawQuery("
    SELECT cv_idx, cv_name, cv_type, cv_description, cv_options, cv_required
    FROM chatbot_variable_t
    WHERE ct_idx = ?
    ORDER BY cv_order",
    [$categoryId]
);

// 변수 타입 매핑
                        $variableTypeMap = [
                            'text' => '텍스트',
                            'select' => '선택',
                            'date' => '날짜',
                            'file' => '파일'
                        ];
?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">챗봇 관리 수정</h4>
                    <form method="post" name="frm_form" id="frm_form" action="./update_chatbot.php">
                        <input type="hidden" name="ct_idx" value="<?= $categoryId ?>" />
                        <input type="hidden" name="deleted_variables" id="deleted_variables" value="" />
                        
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">챗봇 이름 <b class="text-danger">*</b></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($category['parent_name']) ?>" readonly />
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">챗봇 설명 <b class="text-danger">*</b></label>
                            <div class="col-sm-10">
                                <div class="position-relative">
                                    <input type="hidden" name="parent_idx" value="<?= $category['parent_idx'] ?>" />
                                    <textarea class="form-control" name="bot_description" id="bot_description" rows="3" 
                                        placeholder="챗봇에 대한 설명을 입력해주세요" 
                                        maxlength="40"
                                        onkeyup="checkLength(this)"><?= htmlspecialchars($botDescription['cd_description'] ?? '') ?></textarea>
                                    <div class="text-right text-muted mt-1">
                                        <small><span id="bot_description_length">0</span>/40자</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">카테고리 <b class="text-danger">*</b></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="category_name" value="<?= htmlspecialchars($category['ct_name']) ?>" />
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">요구 포인트</label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control" name="required_point" value="<?= htmlspecialchars($category['ct_required_point']) ?>" />
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">프롬프트 <b class="text-danger">*</b></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control mb-2" name="prompt_title" value="<?= htmlspecialchars($prompt['cp_title']) ?>" placeholder="프롬프트 제목" />
                                <textarea class="form-control" name="prompt_content" rows="4" placeholder="프롬프트 내용"><?= htmlspecialchars($prompt['cp_content']) ?></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">변수 <b class="text-danger">*</b></label>
                            <div class="col-sm-10" id="variables-container">
                                <?php foreach ($variables as $variable): ?>
                                <div class="variable-item mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <input type="hidden" name="variable_idx[]" value="<?= $variable['cv_idx'] ?>" />
                                        <input type="text" class="form-control flex-grow-1 mr-2" name="variable_name[]" value="<?= htmlspecialchars($variable['cv_name']) ?>" />
                                        <select class="form-control mr-2 variable-type-select" name="variable_type[]" style="width: auto; min-width: 120px;">
                                            <?php foreach ($variableTypeMap as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $variable['cv_type'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="custom-control custom-checkbox mr-2">
                                            <input type="checkbox" class="custom-control-input" name="variable_required[]" 
                                                   id="required_<?= $variable['cv_idx'] ?>" 
                                                   value="<?= $variable['cv_idx'] ?>"
                                                   <?= $variable['cv_required'] === 'Y' ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="required_<?= $variable['cv_idx'] ?>">필수</label>
                                        </div>
                                        <input type="button" class="btn btn-outline-danger btn-sm" value="삭제" onclick="f_variable_del(this);">
                                    </div>
                                    <div class="select-options-container" style="display: <?= $variable['cv_type'] === 'select' ? 'block' : 'none' ?>;">
                                        <input type="text" class="form-control mb-2" name="variable_options[]" 
                                            value="<?= htmlspecialchars($variable['cv_options'] ? implode(', ', json_decode($variable['cv_options'], true)) : '') ?>" 
                                            placeholder="선택 옵션을 쉼표(,)로 구분하여 입력해주세요. 예: 옵션1,옵션2,옵션3" />
                                    </div>
                                    <input type="text" class="form-control" name="variable_desc[]" value="<?= htmlspecialchars($variable['cv_description']) ?>" />
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="col-sm-10 offset-sm-2">
                                <button type="button" class="btn btn-primary btn-sm btn-add d-inline-flex align-items-center">
                                    <i class="mdi mdi-plus mr-1"></i>
                                    <span>변수 추가</span>
                                </button>
                            </div>
                        </div>

                        <p class="p-3 text-center">
                            <input type="submit" value="저장" class="btn btn-outline-primary" />
                            <input type="button" value="취소" onclick="history.go(-1);" class="btn btn-outline-secondary mx-2" />
                        </p>
                    </form>

                    <style>
                    /* 선택 박스 최대 너비 제한 */
                    select.form-control {
                        max-width: 300px;
                    }

                    /* 입력 필드가 남은 공간을 모두 차지하도록 설정 */
                    .flex-grow-1 {
                        flex: 1;
                        min-width: 0;
                    }

                    select[name="variable_type[]"] {
                        max-width: 120px;
                    }

                    /* 변수 추가 버튼 스타일 */
                    .btn-add {
                        padding: 8px 20px;
                    }

                    .btn-add i {
                        font-size: 16px;
                    }

                    .btn-add span {
                        line-height: 16px;
                    }
                    </style>

                    <script>
                    $(document).ready(function() {
                        // 변수 추가 버튼 클릭
                        $('.btn-add').click(function() {
                            const timestamp = Date.now(); // 고유 ID 생성용
                            const template = `
                                <div class="variable-item mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <input type="hidden" name="variable_idx[]" value="" />
                                        <input type="text" class="form-control flex-grow-1 mr-2" name="variable_name[]" placeholder="변수 이름" />
                                        <select class="form-control mr-2 variable-type-select" name="variable_type[]" style="width: auto; min-width: 120px;">
                                            <?php foreach ($variableTypeMap as $value => $label): ?>
                                            <option value="<?= $value ?>"><?= $label ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="custom-control custom-checkbox mr-2">
                                            <input type="checkbox" class="custom-control-input" name="variable_required[]" 
                                                   id="required_new_${timestamp}" 
                                                   value="new_${timestamp}" checked>
                                            <label class="custom-control-label" for="required_new_${timestamp}">필수</label>
                                        </div>
                                        <input type="button" class="btn btn-outline-danger btn-sm" value="삭제" onclick="f_variable_del(this);">
                                    </div>
                                    <div class="select-options-container" style="display: none;">
                                        <input type="text" class="form-control mb-2" name="variable_options[]" 
                                            placeholder="선택 옵션을 쉼표(,)로 구분하여 입력해주세요. 예: 옵션1,옵션2,옵션3" />
                                    </div>
                                    <input type="text" class="form-control" name="variable_desc[]" placeholder="변수 내용" />
                                </div>
                            `;
                            $('#variables-container').append(template);
                        });

                        // 폼 유효성 검사
                        $("#frm_form").validate({
                            submitHandler: function() {
                                var f = document.frm_form;

                                if (!f.category_name.value) {
                                    jalert("카테고리 이름을 입력해주세요.");
                                    f.category_name.focus();
                                    return false;
                                }

                                if (!f.required_point.value) {
                                    jalert("요구 포인트를 입력해주세요.");
                                    f.required_point.focus();
                                    return false;
                                }

                                if (!f.prompt_title.value) {
                                    jalert("프롬프트 제목을 입력해주세요.");
                                    f.prompt_title.focus();
                                    return false;
                                }

                                if (!f.prompt_content.value) {
                                    jalert("프롬프트 내용을 입력해주세요.");
                                    f.prompt_content.focus();
                                    return false;
                                }

                                // 최소 하나의 변수 확인
                                if ($('.variable-item').length === 0) {
                                    jalert("최소 하나의 변수를 입력해주세요.");
                                    return false;
                                }

                                // 변수 입력값 검증
                                let isValid = true;
                                $('.variable-item').each(function() {
                                    const name = $(this).find('input[name="variable_name[]"]').val();
                                    const desc = $(this).find('input[name="variable_desc[]"]').val();
                                    
                                    if (!name || !desc) {
                                        jalert("모든 변수의 이름과 내용을 입력해주세요.");
                                        isValid = false;
                                        return false;
                                    }
                                });

                                if (!isValid) return false;

                                // 폼 유효성 검사에 설명 필드 검증 추가
                                if (!f.bot_description.value) {
                                    jalert("챗봇 설명을 입력해주세요.");
                                    f.bot_description.focus();
                                    return false;
                                }

                                return true;
                            }
                        });

                        $("#frm_form").submit(function(e) {
                            e.preventDefault();
                            
                            // 현재 표시된 변수들의 ID를 수집
                            let currentVariableIds = [];
                            $('.variable-item input[name="variable_idx[]"]').each(function() {
                                let val = $(this).val();
                                if (val) currentVariableIds.push(val);
                            });
                            
                            // 원래 있던 모든 변수 ID와 현재 표시된 변수 ID를 비교하여 삭제된 변수 찾기
                            let originalVariableIds = <?= json_encode(array_column($variables, 'cv_idx')) ?>;
                            let deletedVariables = originalVariableIds.filter(id => !currentVariableIds.includes(id));
                            
                            // 삭제된 변수 ID를 hidden input에 설정
                            $('#deleted_variables').val(deletedVariables.join(','));
                            
                            // 폼 데이터 준비
                            var formData = $(this).serialize();
                            
                            // Ajax 요청
                            $.ajax({
                                url: $(this).attr('action'),
                                type: 'POST',
                                data: formData,
                                dataType: 'json',
                                beforeSend: function() {
                                    $('#splinner_modal').modal('show');
                                },
                                success: function(response) {
                                    $('#splinner_modal').modal('hide');
                                    if (response.success) {
                                        jalert(response.message, function() {
                                            location.href = './admin_managebot.php';
                                        });
                                    } else {
                                        jalert(response.message || '저장에 실패했습니다.');
                                    }
                                },
                                error: function(xhr, status, error) {
                                    $('#splinner_modal').modal('hide');
                                    console.error('Ajax 오류:', error);
                                    jalert('서버 오류가 발생했습니다.');
                                }
                            });
                        });

                        // 타입 선택 이벤트 핸들러 추가
                        $(document).on('change', '.variable-type-select', function() {
                            const optionsContainer = $(this).closest('.variable-item').find('.select-options-container');
                            if ($(this).val() === 'select') {
                                optionsContainer.slideDown();
                            } else {
                                optionsContainer.slideUp();
                            }
                        });
                    });

                    // 변수 삭제 함수
                    function f_variable_del(obj) {
                        $.confirm({
                            title: '확인',
                            content: '정말 삭제하시겠습니까?',
                            buttons: {
                                확인: function () {
                                    $(obj).closest('.variable-item').remove();
                                },
                                취소: function () {
                                }
                            }
                        });
                    }

                    function checkLength(textarea) {
                        const maxLength = 40;
                        const currentLength = textarea.value.length;
                        document.getElementById('bot_description_length').textContent = currentLength;
                        
                        if (currentLength > maxLength) {
                            textarea.value = textarea.value.substring(0, maxLength);
                        }
                    }

                    // 페이지 로드 시 초기 글자 수 표시
                    document.addEventListener('DOMContentLoaded', function() {
                        const textarea = document.getElementById('bot_description');
                        if (textarea) {
                            checkLength(textarea);
                        }
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include $_SERVER['DOCUMENT_ROOT'] . "/mng/foot.inc.php";
?>