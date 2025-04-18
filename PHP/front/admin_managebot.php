<?php
require_once '../config/database.php';

$variableTypeMap = [
    'text' => '텍스트',
    'select' => '선택',
    'date' => '날짜',
    'file' => '파일'
];
?>
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
                margin: 3.5rem auto 0;
                padding: 1.5rem 10rem;
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
                        // 데이터베이스 연결
                        $db = Database::getInstance()->getConnection();

                        try {
                            // parent_idx가 NULL인 대분류 카테고리만 조회
                            $query = "SELECT ct_idx, ct_name, ct_status FROM category_t WHERE parent_idx IS NULL ORDER BY ct_order ASC";
                            $categories = $db->rawQuery($query);

                            // 현재 선택된 카테고리 정보 저장
                            $selected_category = null;
                        ?>
                        <?php foreach ($categories as $category): 
                            if ($category['ct_name'] === '생활기록부') {
                                $selected_category = $category;
                            }
                        ?>
                            <button class="btn-bot <?= ($category['ct_name'] === '생활기록부') ? 'selected' : '' ?>" 
                                    data-status="<?= $category['ct_status'] ?>" 
                                    data-id="<?= $category['ct_idx'] ?>">
                                <?= htmlspecialchars($category['ct_name']) ?>
                            </button>
                        <?php endforeach; ?>
                        <?php
                        } catch (Exception $e) {
                            echo "카테고리를 불러오는 중 오류가 발생했습니다: " . $e->getMessage();
                        }
                        ?>
                    </div>
                </div>

                <div class="flex-bw my_10">
                    <h2 class="fs_28 fw_600"><?= $selected_category ? htmlspecialchars($selected_category['ct_name']) : '생활기록부' ?></h2>
                    <button class="btn-active <?= ($selected_category && $selected_category['ct_status'] === 'Y') ? 'activated' : '' ?>">
                        <?= ($selected_category && $selected_category['ct_status'] === 'Y') ? '활성화' : '비활성화' ?>
                    </button>
                </div>

                <div id="ai-container">
                    <div id="category-container" class="mb_20">
                        <h2 class="fs_22 fw_600 mb_14">카테고리</h2>
                        <?php
                        try {
                            if ($selected_category) {
                                // 선택된 대분류의 하위 카테고리 조회
                                $sub_query = "SELECT ct_idx, ct_name, ct_status FROM category_t 
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
                                        <input class="custom-input" type="text" value="<?= htmlspecialchars($sub_category['ct_name']) ?>" readonly />
                                        <button class="btn-active <?= $sub_category['ct_status'] === 'Y' ? 'activated' : '' ?>"
                                                data-id="<?= $sub_category['ct_idx'] ?>">
                                            <?= $sub_category['ct_status'] === 'Y' ? '활성화' : '비활성화' ?>
                                        </button>
                                    </label>
                                <?php
                                    $isFirst = false;  // 첫 번째 항목 이후에는 false로 설정
                                }
                            }
                        } catch (Exception $e) {
                            echo "하위 카테고리를 불러오는 중 오류가 발생했습니다: " . $e->getMessage();
                        }
                        ?>
                    </div>
                    <div class="mb_20">
                        <h2 class="fs_22 fw_600 mb_14">프롬프트</h2>
                        <?php
                        // 초기 프롬프트 데이터 가져오기 (첫 번째 하위 카테고리의 프롬프트)
                        try {
                            if ($selected_category && !empty($sub_categories)) {  // $sub_categories는 위에서 이미 조회한 하위 카테고리 목록
                                $first_sub_category = $sub_categories[0];  // 첫 번째 하위 카테고리
                                
                                $prompt_query = "SELECT cp.* FROM chatbot_prompt_t cp
                                               WHERE cp.parent_ct_idx = ? 
                                               AND cp.ct_idx = ?";
                                $prompt = $db->rawQuery($prompt_query, [
                                    $selected_category['ct_idx'],
                                    $first_sub_category['ct_idx']
                                ]);
                                
                                $prompt = !empty($prompt) ? $prompt[0] : null;
                            }
                        } catch (Exception $e) {
                            $prompt = null;
                        }
                        ?>
                        <input class="custom-input mb_10" type="text" id="prompt_title" 
                               value="<?= $prompt ? htmlspecialchars($prompt['cp_title']) : '' ?>" 
                               placeholder="프롬프트 제목" readonly />
                        <textarea class="custom-textarea" id="prompt_content" 
                                  placeholder="프롬프트 내용" readonly><?= $prompt ? htmlspecialchars($prompt['cp_content']) : '' ?></textarea>
                    </div>
                    <div class="mb_20">
                        <h2 class="fs_22 fw_600 mb_14">변수</h2>
                        <?php
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
                                                <input class="custom-input" type="text" value="<?= htmlspecialchars($variable['cv_name']) ?>" readonly />
                                                <div class="dropdown">
                                                    <button class="btn-variable-type fs_15 fw_500" type="button" readonly>
                                                        <?= $variableTypeMap[$variable['cv_type']] ?>
                                                    </button>
                                                </div>
                                            </div>
                                            <input class="custom-input" type="text" value="<?= htmlspecialchars($variable['cv_description']) ?>" readonly />
                                        </div>
                                        <?php
                                    }
                                } else {
                                    echo '<p>등록된 변수가 없습니다.</p>';
                                }
                            }
                        } catch (Exception $e) {
                            echo "변수를 불러오는 중 오류가 발생했습니다: " . $e->getMessage();
                        }
                        ?>
                    </div>
                </div>

                <div class="flex-c gap_20">
                    <button class="btn-accept" onclick="location.href='./admin_managebot_edit.php'">수정하기</button>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // PHP의 variableTypeMap을 JavaScript 변수로 변환
            const variableTypeMap = {
                'text': '텍스트',
                'select': '선택',
                'date': '날짜',
                'file': '파일'
            };

            const botButtons = document.querySelectorAll('.btn-bot');
            const categoryTitle = document.querySelector('.fs_28.fw_600');
            const statusButton = document.querySelector('.flex-bw .btn-active');  // 상위 카테고리 상태 버튼

            // 초기 이벤트 리스너 연결
            attachCategoryRadioListeners();
            attachSubCategoryListeners();

            // 상위 카테고리 상태 버튼 이벤트 리스너
            statusButton.addEventListener('click', async function() {
                const selectedBot = document.querySelector('.btn-bot.selected');
                if (!selectedBot) return;

                const categoryId = selectedBot.dataset.id;
                const currentStatus = selectedBot.dataset.status;
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
                        selectedBot.dataset.status = newStatus;
                        this.classList.toggle('activated');
                        this.textContent = newStatus === 'Y' ? '활성화' : '비활성화';
                    }
                } catch (error) {
                    console.error('상태 업데이트 중 오류:', error);
                    alert('상태 업데이트 중 오류가 발생했습니다.');
                }
            });

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

            // 대분류 카테고리 버튼 클릭 이벤트에서도 하위 카테고리 이벤트 리스너 연결
            botButtons.forEach(button => {
                button.addEventListener('click', async function() {
                    // 모든 버튼에서 selected 클래스 제거
                    botButtons.forEach(btn => btn.classList.remove('selected'));
                    // 클릭된 버튼에 selected 클래스 추가
                    this.classList.add('selected');
                    
                    // 제목 업데이트
                    categoryTitle.textContent = this.textContent.trim();
                    
                    // 상태 버튼 업데이트
                    const status = this.dataset.status;
                    if (status === 'Y') {
                        statusButton.classList.add('activated');
                        statusButton.textContent = '활성화';
                    } else {
                        statusButton.classList.remove('activated');
                        statusButton.textContent = '비활성화';
                    }

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
                                        <input class="custom-input" type="text" value="${category.ct_name}" readonly />
                                        <button class="btn-active ${category.ct_status === 'Y' ? 'activated' : ''}"
                                                data-id="${category.ct_idx}">
                                            ${category.ct_status === 'Y' ? '활성화' : '비활성화'}
                                        </button>
                                    </label>
                                `;
                            });
                        } else {
                            html += '<p>하위 카테고리가 없습니다.</p>';
                        }
                        
                        categoryContainer.innerHTML = html;
                        
                        // 이벤트 리스너 연결
                        attachSubCategoryListeners();
                        attachCategoryRadioListeners();

                        // 첫 번째 하위 카테고리의 프롬프트와 변수 로드 부분 수정
                        const firstRadio = categoryContainer.querySelector('input[type="radio"]');
                        if (firstRadio) {
                            await loadPromptAndVariables(categoryId, firstRadio.value);
                        }
                    } catch (error) {
                        console.error('하위 카테고리 로드 중 오류:', error);
                    }
                });
            });

            // 하위 카테고리 라디오 버튼 이벤트 리스너 함수 수정
            function attachCategoryRadioListeners() {
                const radioButtons = document.querySelectorAll('input[type="radio"][name="category"]');
                radioButtons.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.checked) {
                            const selectedBot = document.querySelector('.btn-bot.selected');
                            if (selectedBot) {
                                loadPromptAndVariables(selectedBot.dataset.id, this.value);
                            }
                        }
                    });
                });
            }

            // loadPromptAndVariables 함수 추가
            async function loadPromptAndVariables(parentCategoryId, categoryId) {
                try {
                    console.log('Loading prompt and variables for:', parentCategoryId, categoryId);

                    // 프롬프트 로드
                    const promptResponse = await fetch(`get_prompt.php?parent_ct_idx=${parentCategoryId}&ct_idx=${categoryId}`);
                    if (!promptResponse.ok) throw new Error('프롬프트 로드 실패');
                    const promptData = await promptResponse.json();
                    
                    // 프롬프트 데이터 업데이트
                    const promptTitle = document.getElementById('prompt_title');
                    const promptContent = document.getElementById('prompt_content');
                    
                    if (promptData.prompt) {
                        promptTitle.value = promptData.prompt.cp_title || '';
                        promptContent.value = promptData.prompt.cp_content || '';
                    } else {
                        promptTitle.value = '';
                        promptContent.value = '';
                    }

                    // 변수 로드
                    await loadVariables(categoryId);
                    
                } catch (error) {
                    console.error('프롬프트와 변수 로드 중 오류:', error);
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
                            // variableTypeMap을 사용하여 타입을 한글로 변환
                            const typeText = variableTypeMap[variable.cv_type] || variable.cv_type;
                            html += `
                                <div class="variable-item">
                                    <div class="flex-c gap_10 mb_10">
                                        <input class="custom-input" type="text" value="${variable.cv_name}" readonly />
                                        <div class="dropdown">
                                            <button class="btn-variable-type fs_15 fw_500" type="button" readonly>
                                                ${typeText}
                                            </button>
                                        </div>
                                    </div>
                                    <input class="custom-input" type="text" value="${variable.cv_description}" readonly />
                                </div>
                            `;
                        });
                    } else {
                        html += '<p>등록된 변수가 없습니다.</p>';
                    }
                    
                    variableContainer.innerHTML = html;
                } catch (error) {
                    console.error('변수 로드 중 오류:', error);
                }
            }
        });
        </script>
    </body>
</html>