<?php
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head.inc.php";
$chk_menu = '13';
$chk_sub_menu = '4';
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head_menu.inc.php";
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">챗봇 생성</h4>
                    <form method="post" name="frm_form" id="frm_form" action="./create_chatbot.php" enctype="multipart/form-data">
                        <input type="hidden" name="act" id="act" value="create" />
                        
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">챗봇 선택/생성 <b class="text-danger">*</b></label>
                            <div class="col-sm-10">
                                <div class="d-flex align-items-center mb-2">
                                    <select class="form-control mr-2" name="bot_select" id="bot_select" style="width: auto; min-width: 200px;">
                                        <option value="">새로운 챗봇 생성</option>
                                        <?php
                                        // 기존 챗봇 목록 가져오기
                                        $existing_bots = $DB->rawQuery("SELECT ct_idx, ct_name FROM category_t WHERE parent_idx IS NULL ORDER BY ct_order");
                                        foreach ($existing_bots as $bot) {
                                            $selected = (isset($_GET['parent_idx']) && $_GET['parent_idx'] == $bot['ct_idx']) ? 'selected' : '';
                                            echo "<option value='{$bot['ct_idx']}' {$selected}>{$bot['ct_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                    <input type="text" class="form-control flex-grow-1" name="bot_name" id="bot_name" placeholder="새로운 챗봇 이름 입력" />
                                </div>
                            </div>
                        </div>

                        <!-- 챗봇 설명 입력 필드 추가 -->
                        <div class="form-group row" id="bot_description_container">
                            <label class="col-sm-2 col-form-label">챗봇 설명 <b class="text-danger">*</b></label>
                            <div class="col-sm-10">
                                <div class="position-relative">
                                    <textarea class="form-control" name="bot_description" id="bot_description" rows="3" 
                                        placeholder="챗봇에 대한 설명을 입력해주세요" 
                                        maxlength="40"
                                        onkeyup="checkLength(this)"></textarea>
                                    <div class="text-right text-muted mt-1">
                                        <small><span id="bot_description_length">0</span>/40자</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">카테고리 <b class="text-danger">*</b></label>
                            <div class="col-sm-10">
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center">
                                        <input type="text" class="form-control" name="category_name" placeholder="카테고리 이름" />
                                    </div>
                                    <div class="invalid-feedback mt-1"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">요구 포인트</label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control" name="required_point" placeholder="요구 포인트" value="10" min="0" />
                                <small class="form-text text-muted">기본값: 10</small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">프롬프트 <b class="text-danger">*</b></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control mb-2" name="prompt_title" placeholder="프롬프트 제목" />
                                <textarea class="form-control" name="prompt_content" rows="4" placeholder="프롬프트 내용"></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">변수 <b class="text-danger">*</b></label>
                            <div class="col-sm-10" id="variables-container">
                                <div class="variable-item mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <input type="text" class="form-control flex-grow-1 mr-2" name="variable_name[]" placeholder="변수 이름" />
                                        <select class="form-control mr-2 variable-type-select" name="variable_type[]" style="width: auto; min-width: 120px;">
                                            <option value="text">텍스트</option>
                                            <option value="select">선택</option>
                                            <option value="date">날짜</option>
                                            <option value="file">파일</option>
                                        </select>
                                        <div class="custom-control custom-checkbox mr-2">
                                            <input type="checkbox" class="custom-control-input" name="variable_required[]" id="required_${Date.now()}" checked>
                                            <label class="custom-control-label" for="required_${Date.now()}">필수</label>
                                        </div>
                                        <input type="button" class="btn btn-outline-danger btn-sm" value="삭제" onclick="f_variable_del(this);">
                                    </div>
                                    <div class="select-options-container" style="display: none;">
                                        <input type="text" class="form-control mb-2" name="variable_options[]" placeholder="선택 옵션을 쉼표(,)로 구분하여 입력해주세요. 예: 옵션1,옵션2,옵션3" />
                                    </div>
                                    <input type="text" class="form-control" name="variable_desc[]" placeholder="변수 내용" />
                                </div>
                            </div>
                            <div class="col-sm-10 offset-sm-2">
                                <button type="button" class="btn btn-primary btn-sm btn-add d-inline-flex align-items-center">
                                    <i class="mdi mdi-plus mr-1"></i>
                                    <span>변수 추가</span>
                                </button>
                            </div>
                        </div>

                        <p class="p-3 text-center">
                            <input type="submit" value="확인" class="btn btn-outline-primary" />
                            <input type="button" value="목록" onclick="location.href='./admin_managebot.php';" class="btn btn-outline-secondary mx-2" />
                        </p>
                    </form>

                    <script>
                    $(document).ready(function() {
                        const botSelect = $('#bot_select');
                        const botNameInput = $('#bot_name');
                        const categoryInput = $('input[name="category_name"]');
                        const botDescriptionTextarea = $('#bot_description');
                        let existingCategories = []; // 기존 카테고리 목록 저장
                        
                        // 챗봇 선택 시 카테고리 목록 가져오기
                        function loadExistingCategories(parentId) {
                            if (!parentId) {
                                existingCategories = [];
                                return;
                            }
                            
                            // 기존 카테고리 목록 가져오기
                            $.ajax({
                                url: 'get_categories.php',
                                type: 'GET',
                                data: { parent_idx: parentId },
                                dataType: 'json',
                                success: function(data) {
                                    existingCategories = data.map(category => category.ct_name.toLowerCase());
                                    
                                    // 현재 입력된 카테고리 이름 검사
                                    const currentCategoryName = categoryInput.val().toLowerCase();
                                    const feedbackDiv = categoryInput.parent().siblings('.invalid-feedback');
                                    
                                    if (existingCategories.includes(currentCategoryName)) {
                                        feedbackDiv.text('이미 존재하는 카테고리 이름입니다.').show();
                                        categoryInput.addClass('is-invalid');
                                    } else {
                                        feedbackDiv.text('').hide();
                                        categoryInput.removeClass('is-invalid');
                                    }
                                },
                                error: function(error) {
                                    console.error('카테고리 로드 오류:', error);
                                    existingCategories = [];
                                }
                            });
                        }
                        
                        // 카테고리 이름 입력 시 중복 체크
                        categoryInput.on('input', function() {
                            const inputValue = $(this).val().toLowerCase();
                            const feedbackDiv = $(this).parent().siblings('.invalid-feedback');
                            
                            if (existingCategories.includes(inputValue)) {
                                feedbackDiv.text('이미 존재하는 카테고리 이름입니다.').show();
                                $(this).addClass('is-invalid');
                            } else {
                                feedbackDiv.text('').hide();
                                $(this).removeClass('is-invalid');
                            }
                        });
                        
                        // 초기 상태 설정
                        function updateBotNameState() {
                            const selectedValue = botSelect.val();
                            if (selectedValue) {
                                // 기존 챗봇 선택 시
                                botNameInput.val('').prop('disabled', true).prop('required', false);
                                botNameInput.css('background-color', '#e9ecef');
                                
                                // 설명 필드도 readonly로 변경
                                fetch(`get_chatbot_description.php?ct_idx=${selectedValue}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            botDescriptionTextarea.val(data.description)
                                                .prop('readonly', true)
                                                .css('background-color', '#e9ecef')
                                                .css('cursor', 'not-allowed');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('설명 로드 오류:', error);
                                        botDescriptionTextarea.val('')
                                            .prop('readonly', false)
                                            .css('background-color', '')
                                            .css('cursor', '');
                                    });
                            } else {
                                // 새로운 챗봇 생성 시
                                botNameInput.prop('disabled', false).prop('required', true);
                                botNameInput.css('background-color', '');
                                
                                // 설명 필드 초기화 및 편집 가능하게 변경
                                botDescriptionTextarea.val('')
                                    .prop('readonly', false)
                                    .css('background-color', '')
                                    .css('cursor', '');
                            }
                        }

                        // 챗봇 선택 변경 시 이벤트
                        botSelect.change(function() {
                            updateBotNameState();
                            // 선택된 챗봇의 카테고리 목록 가져오기
                            loadExistingCategories($(this).val());
                        });

                        // 페이지 로드 시 초기 실행
                        updateBotNameState();

                        // 변수 추가 버튼 클릭
                        $('.btn-add').click(function() {
                            const variableTemplate = `
                                <div class="variable-item mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <input type="text" class="form-control flex-grow-1 mr-2" name="variable_name[]" placeholder="변수 이름" />
                                        <select class="form-control mr-2 variable-type-select" name="variable_type[]" style="width: auto; min-width: 120px;">
                                            <option value="text">텍스트</option>
                                            <option value="select">선택</option>
                                            <option value="date">날짜</option>
                                            <option value="file">파일</option>
                                        </select>
                                        <div class="custom-control custom-checkbox mr-2">
                                            <input type="checkbox" class="custom-control-input" name="variable_required[]" id="required_${Date.now()}" checked>
                                            <label class="custom-control-label" for="required_${Date.now()}">필수</label>
                                        </div>
                                        <input type="button" class="btn btn-outline-danger btn-sm" value="삭제" onclick="f_variable_del(this);">
                                    </div>
                                    <div class="select-options-container" style="display: none;">
                                        <input type="text" class="form-control mb-2" name="variable_options[]" placeholder="선택 옵션을 쉼표(,)로 구분하여 입력해주세요. 예: 옵션1,옵션2,옵션3" />
                                    </div>
                                    <input type="text" class="form-control" name="variable_desc[]" placeholder="변수 내용" />
                                </div>
                            `;
                            $('#variables-container').append(variableTemplate);
                        });

                        // 폼 유효성 검사
                        $("#frm_form").validate({
                            submitHandler: function() {
                                var f = document.frm_form;

                                // 챗봇 선택 또는 이름 입력 검증
                                if (!f.bot_select.value && !f.bot_name.value) {
                                    jalert("챗봇을 선택하거나 새로운 챗봇 이름을 입력해주세요.");
                                    f.bot_name.focus();
                                    return false;
                                }

                                if (!f.bot_select.value && f.bot_name.value.length < 2) {
                                    jalert("챗봇 이름은 최소 2자 이상 입력해주세요.");
                                    f.bot_name.focus();
                                    return false;
                                }

                                // 필수 입력 검증
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

                                // 카테고리 중복 검사
                                const categoryName = f.category_name.value.toLowerCase();
                                if (existingCategories.includes(categoryName)) {
                                    jalert("이미 존재하는 카테고리 이름입니다.");
                                    f.category_name.focus();
                                    return false;
                                }

                                // 폼 검증에 설명 필드 추가
                                if (!f.bot_description.value) {
                                    jalert("챗봇 설명을 입력해주세요.");
                                    f.bot_description.focus();
                                    return false;
                                }

                                // 선택형 변수의 옵션 검증
                                $('.variable-type-select').each(function() {
                                    if ($(this).val() === 'select') {
                                        const optionsInput = $(this).closest('.variable-item').find('input[name="variable_options[]"]');
                                        if (!optionsInput.val()) {
                                            jalert("선택형 변수의 옵션을 입력해주세요.");
                                            optionsInput.focus();
                                            return false;
                                        }
                                    }
                                });

                                // Ajax 제출
                                $.ajax({
                                    url: './create_chatbot.php',
                                    type: 'POST',
                                    data: new FormData(f),
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
                                                location.href = './admin_managebot.php';
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

                                return false; // 폼 기본 제출 방지
                            },
                            rules: {
                                bot_name: {
                                    required: true,
                                    minlength: 2
                                },
                                category_name: {
                                    required: true
                                },
                                prompt_title: {
                                    required: true
                                },
                                prompt_content: {
                                    required: true
                                }
                            },
                            messages: {
                                bot_name: {
                                    required: "챗봇 이름을 입력해주세요.",
                                    minlength: "챗봇 이름은 최소 2자 이상 입력해주세요."
                                },
                                category_name: {
                                    required: "카테고리 이름을 입력해주세요."
                                },
                                prompt_title: {
                                    required: "프롬프트 제목을 입력해주세요."
                                },
                                prompt_content: {
                                    required: "프롬프트 내용을 입력해주세요."
                                }
                            },
                            errorPlacement: function(error, element) {
                                $(element)
                                    .closest("form")
                                    .find("span[for='" + element.attr("id") + "']")
                                    .append(error);
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

                    // 타입 선택 이벤트 핸들러
                    function handleVariableTypeChange(select) {
                        const optionsContainer = $(select).closest('.variable-item').find('.select-options-container');
                        if ($(select).val() === 'select') {
                            optionsContainer.slideDown();
                        } else {
                            optionsContainer.slideUp();
                        }
                    }

                    // 페이지 로드 시 이벤트 바인딩
                    $(document).ready(function() {
                        // 기존 코드에 추가...
                        
                        // 변수 타입 변경 이벤트 처리
                        $(document).on('change', '.variable-type-select', function() {
                            handleVariableTypeChange(this);
                        });

                        // 변수 추가 버튼 클릭 시 새로운 템플릿 사용
                        $('.btn-add').click(function() {
                            $('#variables-container').append(variableTemplate);
                        });

                        // 폼 제출 시 유효성 검사에 옵션 검사 추가
                        $("#frm_form").validate({
                            submitHandler: function() {
                                // 기존 검증 코드...

                                // 선택형 변수의 옵션 검증
                                $('.variable-type-select').each(function() {
                                    if ($(this).val() === 'select') {
                                        const optionsInput = $(this).closest('.variable-item').find('input[name="variable_options[]"]');
                                        if (!optionsInput.val()) {
                                            jalert("선택형 변수의 옵션을 입력해주세요.");
                                            optionsInput.focus();
                                            return false;
                                        }
                                    }
                                });

                                // 나머지 제출 코드...
                            }
                        });
                    });
                    </script>

                    <style>
                    /* 비활성화된 입력 필드 스타일 */
                    input:disabled {
                        background-color: #e9ecef !important;
                        cursor: not-allowed !important;
                        opacity: 0.7 !important;
                    }

                    /* 선택 박스 최대 너비 제한 */
                    select.form-control {
                        max-width: 300px;
                    }

                    /* 입력 필드가 남은 공간을 모두 차지하도록 설정 */
                    .flex-grow-1 {
                        flex: 1;
                        min-width: 0; /* 오버플로우 방지 */
                    }

                    /* 변수 타입 선택 박스 스타일 */
                    select[name="variable_type[]"] {
                        max-width: 120px;
                    }

                    /* 변수 추가 버튼 스타일만 유지 */
                    .btn-add {
                        padding: 8px 20px;
                    }

                    .btn-add i {
                        font-size: 16px;
                    }

                    .btn-add span {
                        line-height: 16px;
                    }

                    .invalid-feedback {
                        display: none;
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 80%;
                        color: #dc3545;
                    }

                    input.is-invalid {
                        border-color: #dc3545;
                    }

                    input.is-invalid:focus {
                        border-color: #dc3545;
                        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
                    }

                    .d-flex.flex-column {
                        position: relative;
                    }
                    </style>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include $_SERVER['DOCUMENT_ROOT'] . "/mng/foot.inc.php";
?>