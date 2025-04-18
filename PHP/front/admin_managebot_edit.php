<!DOCTYPE html>
<html>
    <head>
        <title>메인페이지</title>
        <link rel="stylesheet" href="../css/boot_custom.css">
        <link rel="stylesheet" href="../css/design.css">
        <link rel="stylesheet" href="../css/custom.css">
        <link rel="stylesheet" href="../css/default_dev.css">
        <link rel="stylesheet" href="../css/flatpickr.min.css">
        <link rel="stylesheet" href="../css/admin.css">
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.bundle.min.js"></script>
        <script src="../js/flatpickr.min.js"></script>
        <script src="../js/flatpickr.ko.js"></script>
        <script src="../js/jquery.validate.min.js"></script>
        <style>
            #ai-container {
                min-height: calc(100vh - 430px);
                max-height: calc(100vh - 430px);
            } .btn-accept {
                margin: 3.5rem 0 0 auto;
                padding: 1.5rem 5rem;
            }
        </style>
    </head>
    <body>
        <div class="d-flex">
            <!-- 사이드바 -->
            <div id="sidebar">
                <div id="adminLogo" class="logo mb-5">
                    <img src="../img/admin_logo.svg" alt="쌤공" class="mr-1">
                </div>
                <div class="menu">
                    <a href="./admin_managebot.php" class="menu-item active">
                        <img src="../img/dashboard.svg" alt="">
                        챗봇 관리
                    </a>
                    <a href="./admin_result.php" class="menu-item">
                        <img src="../img/dashboard.svg" alt="">
                        결과 조회
                    </a>
                    <a href="./admin_point.php" class="menu-item">
                        <img src="../img/dashboard.svg" alt="">
                        포인트 조회
                    </a>
                    <a href="./admin_createbot.php" class="menu-item">
                        <img src="../img/dashboard.svg" alt="">
                        챗봇 생성
                    </a>
                </div>
            </div>
    
            <!-- 메인 컨텐츠 -->
            <div id="main-container">
                <div class="flex-bw mt_10 mb_50">
                    <div class="flex-c">
                        <a href="./" class="mr_20 logo"><img src="../img/logo.svg"></a>
                        <div class="relative">
                            <form class="sch_ip align-items-center" method="get" name="frm_search" id="frm_search" action="./search_update" novalidate="novalidate">
                                <select id="search_select" name="search_option" style="    margin-left: 5px;">
                                    <option value="title">제목</option>
                                    <option value="content">내용</option>
                                    <option value="author">작성자</option>
                                </select>
                                <input type="search" id="searchinput" name="searchinput" class="form-control fs_14 flex-fill border-0" placeholder="검색어를 입력해주세요" value="">
                                <button type="submit" class="btn btn-icon flex-shrink-0"><img src="../img/ico_search.svg" style="width:2rem;"></button>
                            </form>
                            <script>
                                if ($.fn.validate) {  // validate 플러그인이 있을 때만 실행
                                    $("#frm_search").validate({
                                        submitHandler: function() {
                                            return true;
                                        },
                                        rules: {
                                            searchinput: {
                                                required: true,
                                            },
                                        },
                                        messages: {
                                            searchinput: {
                                                required: "검색어를 입력해주세요",
                                            },
                                        },
                                        errorPlacement: function(error, element) {
                                            $(element)
                                                .closest("form")
                                                .find("span[for='" + element.attr("id") + "']")
                                                .append(error);
                                        },
                                    });
                                }
                            </script>
                            <div class="search-bot-box" id="searchbox">
                            <div class="recent-search">
                                <h3 class="fs_17 fw_700">최근 검색어</h3>
                                <ul>
                                    <ul>
                                        <li>최근 검색어가 없습니다.</li>
                                    </ul>
                                </ul>
                            </div>
                            <div class="recommended-search">
                                <h3 class="fs_17 fw_700">추천 검색어</h3>
                                <ul>
                                    <li>
                                        <a href="./search_update?searchinput=비상연락망"><p>비상연락망</p></a>
                                    </li>
                                    <li>
                                        <a href="./search_update?searchinput=이름표"><p>이름표</p></a>
                                    </li>
                                    <li>
                                        <a href="./search_update?searchinput=학급 홈페이지"><p>학급 홈페이지</p></a>
                                    </li>
                                    <li>
                                        <a href="./search_update?searchinput=기초조사서"><p>기초조사서</p></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <script>
                        
                            document.addEventListener('DOMContentLoaded', (event) => {
                                const searchInput = document.getElementById('searchinput');
                                const searchBox = document.getElementById('searchbox');
                                const mobileSearchInput = document.getElementById('mobileSearchInput');
                                const body = document.body;
                        
                                function handleSearchInputClick(event) {
                                    console.log('Search input clicked'); // Debug log
                                    searchBox.classList.add('active');
                                    body.classList.add('no-scroll');
                                    mobileSearchInput.focus(); // 모바일 검색 input에 포커스
                                    event.stopPropagation(); // 이벤트 전파 막기
                                }
                        
                                function handleDocumentClick(event) {
                                    if (!searchBox.contains(event.target) && !searchInput.contains(event.target)) {
                                        console.log('Click outside searchbox'); // Debug log
                                        searchBox.classList.remove('active');
                                        body.classList.remove('no-scroll');
                                    }
                                }
                        
                                function handleBackButtonClick(event) {
                                    console.log('Back button clicked'); // Debug log
                                    searchBox.classList.remove('active');
                                    body.classList.remove('no-scroll');
                                    event.stopPropagation(); // 이벤트 전파 막기
                                }
                        
                                searchInput.addEventListener('click', handleSearchInputClick);
                                document.addEventListener('click', handleDocumentClick);
                            });
                        </script>
                        </div>
                    </div>
                    <div class="flex-c">
                        <!-- 로그인 -->
                        <div class="flex-c">
                            <div class="hd_profile">
                                <img src="https://ssemgong.com/uploads/mt_image1_1072_1_1743442040.png" alt="Kou" onerror="this.src=\'https://ssemgong.com/img/profile_no_img.svg\'">
                            </div>
                            <div class="hd-name">
                                <div class="flex-c">
                                    <p class="fw_300 fs_14 mr_10 lh-25">Kou</p><!-- 이름 -->
                                    <img src="../img/ic_ip_select.svg">
                                </div>
                                <ul>
                                    <li><a href="./mypage">마이페이지</a></li>
                                    <li><a href="./logout">로그아웃</a></li>
                                    <li><a href="./community_faq">고객센터</a></li>
                                </ul>
                            </div>
                            <div class="hb-point">
                                <p>0P</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-bw my_10">
                    <h2 class="fs_32 fw_600">챗봇 관리</h2>
                </div>

                <div id="scroll-ai-container" class="mb_20">
                    <div class="button-horizontal-wrapper">
                        <?php
                        require_once '../config/database.php';
                        // 데이터베이스 연결
                        $db = Database::getInstance()->getConnection();

                        try {
                            // parent_idx가 NULL인 대분류 카테고리만 조회
                            $query = "SELECT ct_idx, ct_name, ct_status FROM category_t WHERE parent_idx IS NULL ORDER BY ct_order ASC";
                            $categories = $db->rawQuery($query);

                            // 현재 선택된 카테고리 정보 저장 (기본값: 생활기록부)
                            $selected_category = null;
                            
                            foreach ($categories as $category): 
                                if ($category['ct_name'] === '생활기록부') {
                                    $selected_category = $category;
                                }
                            ?>
                                <button class="btn-bot <?= ($category['ct_name'] === '생활기록부') ? 'selected' : '' ?>" 
                                        data-status="<?= $category['ct_status'] ?>" 
                                        data-id="<?= $category['ct_idx'] ?>">
                                    <?= htmlspecialchars($category['ct_name']) ?>
                                </button>
                            <?php endforeach;
                        } catch (Exception $e) {
                            echo "카테고리를 불러오는 중 오류가 발생했습니다: " . $e->getMessage();
                        }
                        ?>
                    </div>
                </div>

                <div class="flex-bw my_10">
                    <h2 class="fs_28 fw_600"><?= $selected_category ? htmlspecialchars($selected_category['ct_name']) : '생활기록부' ?></h2>
                </div>

                <div id="ai-container">
                    <div id="category-container" class="mb_20">
                        <h2 class="fs_22 fw_600 mb_14">카테고리</h2>
                        <?php
                        try {
                            if ($selected_category) {
                                // 선택된 대분류의 하위 카테고리 조회
                                $sub_query = "SELECT ct_idx, ct_name FROM category_t 
                                             WHERE parent_idx = ? 
                                             ORDER BY ct_order ASC";
                                $sub_categories = $db->rawQuery($sub_query, [$selected_category['ct_idx']]);

                                // 첫 번째 항목 체크를 위한 변수
                                $isFirst = true;

                                foreach ($sub_categories as $sub_category) {
                                ?>
                                    <label class="d-flex align-items-center gap_10 mb_12">
                                        <input type="radio" name="category" value="<?= $sub_category['ct_idx'] ?>" 
                                               <?= $isFirst ? 'checked' : '' ?>>
                                        <input class="custom-input" type="text" value="<?= htmlspecialchars($sub_category['ct_name']) ?>" />
                                        <button class="btn-delete" data-id="<?= $sub_category['ct_idx'] ?>">삭제하기</button>
                                    </label>
                                <?php
                                    $isFirst = false;  // 첫 번째 항목 이후에는 false로 설정
                                }
                            }
                        } catch (Exception $e) {
                            echo "하위 카테고리를 불러오는 중 오류가 발생했습니다: " . $e->getMessage();
                        }
                        ?>
                        <button class="btn-add"><img src="../img/plus.svg" alt="추가하기"></button>
                    </div>
                    <div class="mb_20">
                        <h2 class="fs_22 fw_600 mb_14">프롬프트</h2>
                        <?php
                        try {
                            if ($selected_category && !empty($sub_categories)) {
                                $first_sub_category = $sub_categories[0];
                                
                                $prompt_query = "SELECT cp.* FROM chatbot_prompt_t cp
                                               WHERE cp.parent_ct_idx = ? 
                                               AND cp.ct_idx = ?";
                                $prompt = $db->rawQuery($prompt_query, [
                                    $selected_category['ct_idx'],
                                    $first_sub_category['ct_idx']
                                ]);
                                
                                $prompt = !empty($prompt) ? $prompt[0] : null;
                                ?>
                                <input class="custom-input mb_10" type="text" id="cp_title" 
                                       value="<?= $prompt ? htmlspecialchars($prompt['cp_title']) : '' ?>" 
                                       placeholder="프롬프트 제목" />
                                <textarea class="custom-textarea" id="cp_content" 
                                          placeholder="프롬프트 내용"><?= $prompt ? htmlspecialchars($prompt['cp_content']) : '' ?></textarea>
                                <?php
                            }
                        } catch (Exception $e) {
                            echo "프롬프트를 불러오는 중 오류가 발생했습니다: " . $e->getMessage();
                        }
                        ?>
                    </div>
                    <div class="mb_20">
                        <h2 class="fs_22 fw_600 mb_14">변수</h2>
                        <?php
                        // 변수 타입 매핑 배열 추가 (PHP 상단에)
                        $variableTypeMap = [
                            'text' => '텍스트',
                            'select' => '선택',
                            'date' => '날짜',
                            'file' => '파일'
                        ];

                        try {
                            if ($selected_category && !empty($sub_categories)) {
                                $first_sub_category = $sub_categories[0];
                                
                                $variable_query = "SELECT cv.* FROM chatbot_variable_t cv
                                               WHERE cv.ct_idx = ?
                                               ORDER BY cv_order ASC";
                                $variables = $db->rawQuery($variable_query, [$first_sub_category['ct_idx']]);
                                
                                if (!empty($variables)) {
                                    foreach ($variables as $variable) {
                                        ?>
                                        <div class="variable-item">
                                            <div class="flex-c gap_10 mb_10">
                                                <input class="custom-input" type="text" value="<?= htmlspecialchars($variable['cv_name']) ?>" />
                                                <div class="dropdown" id="variable-type">
                                                    <button class="btn-variable-type fs_15 fw_500" type="button" data-toggle="dropdown" aria-expanded="false">
                                                        <div class="dropdown-text" data-value="<?= $variable['cv_type'] ?>"><?= $variableTypeMap[$variable['cv_type']] ?></div>
                                                        <div class="dropdown-icon polygon-01"></div>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <button class="dropdown-item" data-value="text">텍스트</button>
                                                        <button class="dropdown-item" data-value="select">선택</button>
                                                        <button class="dropdown-item" data-value="date">날짜</button>
                                                        <button class="dropdown-item" data-value="file">파일</button>
                                                    </div>
                                                </div>
                                                <button class="btn-delete" data-id="<?= $variable['cv_idx'] ?>">삭제하기</button>
                                            </div>
                                            <input class="custom-input" type="text" value="<?= htmlspecialchars($variable['cv_description']) ?>" />
                                        </div>
                                        <?php
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            echo "변수를 불러오는 중 오류가 발생했습니다: " . $e->getMessage();
                        }
                        ?>
                        <button class="btn-add"><img src="../img/plus.svg" alt="추가하기"></button>
                    </div>
                </div>

                <div class="flex-c gap_20">
                    <button class="btn-accept">저장하기</button>
                    <button class="btn-cancel" onclick="location.href='./admin_managebot.php'">취소</button>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const botButtons = document.querySelectorAll('.btn-bot');
            const categoryTitle = document.querySelector('.fs_28.fw_600');

            // 초기 이벤트 리스너 연결
            attachCategoryRadioListeners();
            attachVariableTypeListeners();

            // 하위 카테고리 상태 버튼 이벤트 리스너 함수
            function attachSubCategoryListeners() {
                const subStatusButtons = document.querySelectorAll('#category-container .btn-active');
                subStatusButtons.forEach(button => {
                    button.removeEventListener('click', handleSubCategoryStatus);
                    button.addEventListener('click', handleSubCategoryStatus);
                });
            }

            // 하위 카테고리 상태 변경 핸들러
            async function handleSubCategoryStatus() {
                const categoryId = this.dataset.id;
                const currentStatus = this.classList.contains('activated') ? 'Y' : 'N';
                const newStatus = currentStatus === 'Y' ? 'N' : 'Y';

                try {
                    const response = await fetch('update_category_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            category_id: categoryId,
                            status: newStatus
                        })
                    });

                    if (response.ok) {
                        // UI 업데이트
                        this.classList.toggle('activated');
                        this.textContent = newStatus === 'Y' ? '활성화' : '비활성화';
                    }
                } catch (error) {
                    console.error('상태 업데이트 중 오류:', error);
                    alert('상태 업데이트 중 오류가 발생했습니다.');
                }
            }

            // 대분류 카테고리 버튼 클릭 이벤트
            botButtons.forEach(button => {
                button.addEventListener('click', async function() {
                    // 모든 버튼에서 selected 클래스 제거
                    botButtons.forEach(btn => btn.classList.remove('selected'));
                    // 클릭된 버튼에 selected 클래스 추가
                    this.classList.add('selected');
                    
                    // 제목 업데이트
                    categoryTitle.textContent = this.textContent.trim();

                    try {
                        const categoryId = this.dataset.id;
                        const response = await fetch(`get_subcategories.php?parent_id=${categoryId}`);
                        if (!response.ok) throw new Error('하위 카테고리 로드 실패');
                        
                        const data = await response.json();
                        const categoryContainer = document.querySelector('#category-container');
                        
                        let html = '<h2 class="fs_22 fw_600 mb_14">카테고리</h2>';
                        
                        if (data.categories && data.categories.length > 0) {
                            data.categories.forEach((category, index) => {
                                html += `
                                    <label class="d-flex align-items-center gap_10 mb_12">
                                        <input type="radio" name="category" value="${category.ct_idx}" ${index === 0 ? 'checked' : ''}>
                                        <input class="custom-input" type="text" value="${category.ct_name}" />
                                        <button class="btn-delete" data-id="${category.ct_idx}">삭제하기</button>
                                    </label>
                                `;
                            });
                        } else {
                            html += '<p>하위 카테고리가 없습니다.</p>';
                        }
                        
                        html += '<button class="btn-add"><img src="../img/plus.svg" alt="추가하기"></button>';
                        categoryContainer.innerHTML = html;
                        
                        // 이벤트 리스너 다시 연결
                        attachSubCategoryListeners();
                        attachCategoryRadioListeners();

                        // 첫 번째 하위 카테고리의 프롬프트와 변수 로드
                        const firstRadio = categoryContainer.querySelector('input[type="radio"]');
                        if (firstRadio) {
                            await loadPromptAndVariables(categoryId, firstRadio.value);
                        }
                    } catch (error) {
                        console.error('하위 카테고리 로드 중 오류:', error);
                    }
                });
            });

            // 하위 카테고리 라디오 버튼 이벤트 리스너 함수
            function attachCategoryRadioListeners() {
                const radioButtons = document.querySelectorAll('input[type="radio"][name="category"]');
                radioButtons.forEach(radio => {
                    radio.addEventListener('change', async function() {
                        if (this.checked) {
                            const selectedBot = document.querySelector('.btn-bot.selected');
                            if (selectedBot) {
                                const parentCategoryId = selectedBot.dataset.id;
                                const categoryId = this.value;
                                await loadPromptAndVariables(parentCategoryId, categoryId);
                            }
                        }
                    });
                });
            }

            // 변수 타입 매핑 객체 추가 (스크립트 시작 부분에)
            const variableTypeMap = {
                'text': '텍스트',
                'select': '선택',
                'date': '날짜',
                'file': '파일'
            };

            // 역매핑 객체 추가
            const reverseVariableTypeMap = {
                '텍스트': 'text',
                '선택': 'select',
                '날짜': 'date',
                '파일': 'file'
            };

            // 기존의 loadPrompt 함수 내에서 변수도 함께 로드하도록 수정
            async function loadPromptAndVariables(parentCategoryId, categoryId) {
                try {
                    console.log('Loading prompt for:', parentCategoryId, categoryId); // 디버깅용

                    // 프롬프트 로드
                    const promptResponse = await fetch(`get_prompt.php?parent_ct_idx=${parentCategoryId}&ct_idx=${categoryId}`);
                    if (!promptResponse.ok) throw new Error('프롬프트 로드 실패');
                    const promptData = await promptResponse.json();
                    
                    // 프롬프트 데이터 업데이트
                    const promptTitle = document.getElementById('cp_title');
                    const promptContent = document.getElementById('cp_content');
                    
                    if (promptData.prompt) {
                        promptTitle.value = promptData.prompt.cp_title || '';
                        promptContent.value = promptData.prompt.cp_content || '';
                    } else {
                        promptTitle.value = '';
                        promptContent.value = '';
                    }

                    // 변수도 함께 로드
                    await loadVariables(categoryId);
                    
                } catch (error) {
                    console.error('프롬프트 로드 중 오류:', error);
                }
            }

            // loadVariables 함수 수정
            async function loadVariables(categoryId) {
                try {
                    const response = await fetch(`get_variables.php?ct_idx=${categoryId}`);
                    if (!response.ok) throw new Error('변수 로드 실패');
                    
                    const data = await response.json();
                    const variableContainer = document.querySelector('.mb_20:last-of-type');
                    
                    let html = '<h2 class="fs_22 fw_600 mb_14">변수</h2>';
                    
                    if (data.success && data.variables && data.variables.length > 0) {
                        data.variables.forEach(variable => {
                            html += `
                                <div class="variable-item">
                                    <div class="flex-c gap_10 mb_10">
                                        <input class="custom-input" type="text" value="${variable.cv_name}" />
                                        <div class="dropdown" id="variable-type">
                                            <button class="btn-variable-type fs_15 fw_500" type="button" data-toggle="dropdown" aria-expanded="false">
                                                <div class="dropdown-text" data-value="${variable.cv_type}">${variableTypeMap[variable.cv_type]}</div>
                                                <div class="dropdown-icon polygon-01"></div>
                                            </button>
                                            <div class="dropdown-menu">
                                                <button class="dropdown-item" data-value="text">텍스트</button>
                                                <button class="dropdown-item" data-value="select">선택</button>
                                                <button class="dropdown-item" data-value="date">날짜</button>
                                                <button class="dropdown-item" data-value="file">파일</button>
                                            </div>
                                        </div>
                                        <button class="btn-delete" data-id="${variable.cv_idx}">삭제하기</button>
                                    </div>
                                    <input class="custom-input" type="text" value="${variable.cv_description}" />
                                </div>
                            `;
                        });
                    }
                    
                    html += '<button class="btn-add"><img src="../img/plus.svg" alt="추가하기"></button>';
                    variableContainer.innerHTML = html;
                    
                    // 변수 타입 드롭다운 이벤트 리스너 다시 연결
                    attachVariableTypeListeners();
                } catch (error) {
                    console.error('변수 로드 중 오류:', error);
                }
            }

            // 변수 타입 드롭다운 이벤트 리스너
            function attachVariableTypeListeners() {
                document.querySelectorAll('.btn-variable-type').forEach(button => {
                    // 기존 이벤트 리스너 제거
                    button.removeEventListener('click', handleDropdownClick);
                    // 새 이벤트 리스너 추가
                    button.addEventListener('click', handleDropdownClick);
                });

                document.querySelectorAll('.dropdown-item').forEach(item => {
                    // 기존 이벤트 리스너 제거
                    item.removeEventListener('click', handleDropdownItemClick);
                    // 새 이벤트 리스너 추가
                    item.addEventListener('click', handleDropdownItemClick);
                });
            }

            // 드롭다운 클릭 핸들러
            function handleDropdownClick(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const dropdown = this.closest('.dropdown');
                const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                
                // 다른 모든 드롭다운 닫기
                document.querySelectorAll('.dropdown').forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.querySelector('.dropdown-menu')?.classList.remove('show');
                        otherDropdown.querySelector('button').setAttribute('aria-expanded', 'false');
                    }
                });

                // 현재 드롭다운 토글
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
                dropdownMenu.classList.toggle('show');
            }

            // 드롭다운 아이템 클릭 핸들러 수정
            function handleDropdownItemClick(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const dropdown = this.closest('.dropdown');
                const dropdownText = dropdown.querySelector('.dropdown-text');
                const button = dropdown.querySelector('button');
                const value = this.getAttribute('data-value');
                
                dropdownText.setAttribute('data-value', value);
                dropdownText.textContent = variableTypeMap[value];
                dropdown.querySelector('.dropdown-menu').classList.remove('show');
                button.setAttribute('aria-expanded', 'false');
            }

            // 저장하기 버튼 이벤트 리스너 수정
            document.querySelector('.btn-accept').addEventListener('click', async function() {
                const selectedBot = document.querySelector('.btn-bot.selected');
                const selectedCategory = document.querySelector('input[name="category"]:checked');
                
                if (!selectedBot || !selectedCategory) {
                    alert('카테고리를 선택해주세요.');
                    return;
                }

                // 프롬프트 데이터 수집
                const promptData = {
                    parent_ct_idx: selectedBot.dataset.id,
                    ct_idx: selectedCategory.value,
                    cp_title: document.getElementById('cp_title').value,
                    cp_content: document.getElementById('cp_content').value
                };

                // 변수 데이터 수집
                const variables = [];
                let hasEmptyFields = false;

                document.querySelectorAll('.variable-item').forEach((item, index) => {
                    const nameInput = item.querySelector('input.custom-input');
                    const typeText = item.querySelector('.dropdown-text');
                    const descInput = item.querySelectorAll('input.custom-input')[1];
                    
                    if (!nameInput.value || !descInput.value) {
                        hasEmptyFields = true;
                        return;
                    }

                    variables.push({
                        cv_name: nameInput.value,
                        cv_type: typeText.getAttribute('data-value'),  // enum 값 사용
                        cv_description: descInput.value,
                        ct_idx: selectedCategory.value,
                        cv_order: index + 1,
                        cv_wdate: new Date().toISOString().slice(0, 19).replace('T', ' ')
                    });
                });

                if (hasEmptyFields) {
                    alert('모든 변수의 이름과 설명을 입력해주세요.');
                    return;
                }

                try {
                    console.log('전송할 데이터:', { prompt: promptData, variables: variables }); // 디버깅용

                    const response = await fetch('update_chatbot_data.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            prompt: promptData,
                            variables: variables
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    console.log('서버 응답:', result); // 디버깅용

                    if (result.success) {
                        alert('성공적으로 저장되었습니다.');
                        window.location.href = './admin_managebot.php';
                    } else {
                        throw new Error(result.message || '저장 실패');
                    }
                } catch (error) {
                    console.error('저장 중 오류:', error);
                    alert('저장 중 오류가 발생했습니다: ' + error.message);
                }
            });

            // 문서 전체에 이벤트 리스너 추가 (이벤트 위임)
            document.addEventListener('click', async function(e) {
                // 카테고리 삭제 버튼 클릭
                if (e.target.matches('#category-container .btn-delete')) {
                    e.preventDefault();
                    const categoryId = e.target.dataset.id;
                    if (!categoryId) {
                        alert('카테고리 ID를 찾을 수 없습니다.');
                        return;
                    }

                    if (confirm('이 카테고리를 삭제하시겠습니까?')) {
                        try {
                            const response = await fetch('delete_category.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    ct_idx: categoryId
                                })
                            });

                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }

                            const result = await response.json();
                            if (result.success) {
                                // 삭제된 카테고리의 요소 제거
                                const categoryLabel = e.target.closest('label');
                                if (categoryLabel) {
                                    categoryLabel.remove();

                                    // 남은 첫 번째 카테고리 선택
                                    const firstRadio = document.querySelector('input[name="category"]');
                                    if (firstRadio) {
                                        firstRadio.checked = true;
                                        // 선택된 카테고리의 프롬프트와 변수 로드
                                        const selectedBot = document.querySelector('.btn-bot.selected');
                                        if (selectedBot) {
                                            await loadPromptAndVariables(selectedBot.dataset.id, firstRadio.value);
                                        }
                                    } else {
                                        // 남은 카테고리가 없는 경우 프롬프트와 변수 컨테이너 초기화
                                        document.getElementById('cp_title').value = '';
                                        document.getElementById('cp_content').value = '';
                                        const variableContainer = document.querySelector('.mb_20:last-of-type');
                                        variableContainer.innerHTML = `
                                            <h2 class="fs_22 fw_600 mb_14">변수</h2>
                                            <button class="btn-add"><img src="../img/plus.svg" alt="추가하기"></button>
                                        `;
                                    }
                                }
                            } else {
                                throw new Error(result.message || '카테고리 삭제 실패');
                            }
                        } catch (error) {
                            console.error('카테고리 삭제 중 오류:', error);
                            alert('카테고리 삭제 중 오류가 발생했습니다: ' + error.message);
                        }
                    }
                }

                // 변수 삭제 버튼 클릭
                if (e.target.matches('.variable-item .btn-delete')) {
                    e.preventDefault();
                    const variableItem = e.target.closest('.variable-item');
                    if (variableItem && confirm('이 변수를 삭제하시겠습니까?')) {
                        const variableId = e.target.dataset.id;
                        if (variableId) {
                            try {
                                // 서버에 저장된 변수인 경우 삭제 요청
                                const response = await fetch('delete_variable.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        cv_idx: variableId
                                    })
                                });

                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }

                                const result = await response.json();
                                if (!result.success) {
                                    throw new Error(result.message || '변수 삭제 실패');
                                }
                            } catch (error) {
                                console.error('변수 삭제 중 오류:', error);
                                alert('변수 삭제 중 오류가 발생했습니다: ' + error.message);
                                return;
                            }
                        }
                        // UI에서 변수 항목 제거
                        variableItem.remove();
                    }
                }

                // 카테고리 추가 버튼 클릭 핸들러 수정
                if (e.target.matches('#category-container .btn-add img') || 
                    e.target.matches('#category-container .btn-add')) {
                    e.preventDefault();
                    
                    const selectedBot = document.querySelector('.btn-bot.selected');
                    if (!selectedBot) {
                        alert('상위 카테고리를 선택해주세요.');
                        return;
                    }

                    const categoryName = prompt('새로운 카테고리 이름을 입력하세요:');
                    if (!categoryName) return;

                    try {
                        const response = await fetch('add_category.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                parent_idx: selectedBot.dataset.id,
                                ct_name: categoryName
                            })
                        });

                        if (!response.ok) {
                            throw new Error('서버 응답 오류: ' + response.status);
                        }

                        const text = await response.text();
                        console.log('서버 응답:', text);

                        let result;
                        try {
                            result = JSON.parse(text);
                        } catch (e) {
                            console.error('JSON 파싱 오류:', e);
                            throw new Error('서버 응답을 처리할 수 없습니다.');
                        }

                        if (result.success) {
                            const newCategoryHtml = `
                                <label class="d-flex align-items-center gap_10 mb_12">
                                    <input type="radio" name="category" value="${result.ct_idx}">
                                    <input class="custom-input" type="text" value="${categoryName}" />
                                    <button class="btn-delete" data-id="${result.ct_idx}">삭제하기</button>
                                </label>
                            `;
                            
                            const addButton = e.target.closest('.btn-add');
                            addButton.insertAdjacentHTML('beforebegin', newCategoryHtml);

                            // 이벤트 리스너 다시 연결
                            attachCategoryRadioListeners();
                            attachVariableTypeListeners();
                        } else {
                            throw new Error(result.message || '카테고리 추가 실패');
                        }
                    } catch (error) {
                        console.error('카테고리 추가 중 오류:', error);
                        alert('카테고리 추가 중 오류가 발생했습니다: ' + error.message);
                    }
                }

                // 변수 추가 버튼 클릭 처리
                if (e.target.closest('.mb_20:last-of-type .btn-add')) {
                    e.preventDefault();
                    
                    const selectedCategory = document.querySelector('input[name="category"]:checked');
                    if (!selectedCategory) {
                        alert('카테고리를 선택해주세요.');
                        return;
                    }

                    const variableContainer = document.querySelector('.mb_20:last-of-type');
                    const addButton = variableContainer.querySelector('.btn-add');

                    // 새 변수 HTML 생성
                    const newVariableHtml = `
                        <div class="variable-item">
                            <div class="flex-c gap_10 mb_10">
                                <input class="custom-input" type="text" value="새 변수" />
                                <div class="dropdown" id="variable-type">
                                    <button class="btn-variable-type fs_15 fw_500" type="button" data-toggle="dropdown" aria-expanded="false">
                                        <div class="dropdown-text" data-value="text">텍스트</div>
                                        <div class="dropdown-icon polygon-01"></div>
                                    </button>
                                    <div class="dropdown-menu">
                                        <button class="dropdown-item" data-value="text">텍스트</button>
                                        <button class="dropdown-item" data-value="select">선택</button>
                                        <button class="dropdown-item" data-value="date">날짜</button>
                                        <button class="dropdown-item" data-value="file">파일</button>
                                    </div>
                                </div>
                                <button class="btn-delete">삭제하기</button>
                            </div>
                            <input class="custom-input" type="text" value="변수 설명" />
                        </div>
                    `;

                    // 새 변수 추가
                    if (addButton) {
                        addButton.insertAdjacentHTML('beforebegin', newVariableHtml);
                        
                        // 새로 추가된 변수의 드롭다운 이벤트 리스너 연결
                        attachVariableTypeListeners();

                        // 새로 추가된 삭제 버튼에 이벤트 리스너 연결
                        const newDeleteButton = addButton.previousElementSibling.querySelector('.btn-delete');
                        if (newDeleteButton) {
                            newDeleteButton.addEventListener('click', function() {
                                if (confirm('이 변수를 삭제하시겠습니까?')) {
                                    this.closest('.variable-item').remove();
                                }
                            });
                        }
                    }
                }
            });
        });
        </script>
    </body>
</html>