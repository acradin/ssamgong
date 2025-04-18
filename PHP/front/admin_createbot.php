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
        <style>
            #ai-container {
                min-height: calc(100vh - 300px);
                max-height: calc(100vh - 300px);
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
                    <a href="./admin_managebot.php" class="menu-item">
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
                    <a href="./admin_createbot.php" class="menu-item active">
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
                    <h2 class="fs_28 fw_600">챗봇 생성</h2>
                </div>

                <div id="ai-container">
                    <div class="mb_20">
                        <h2 class="fs_22 fw_600 mb_14">챗봇 이름</h2>
                        <input class="custom-input" type="text" placeholder="챗봇 이름 입력" />
                    </div>
                    <div class="mb_20">
                        <h2 class="fs_22 fw_600 mb_14">카테고리</h2>
                        <label class="d-flex align-items-center gap_10 mb_12">
                            <input type="radio" name="category" checked>
                            <input class="custom-input" type="text" placeholder="카테고리 1 이름" />
                            <button class="btn-delete">삭제하기</button>
                        </label>
                    </div>
                    <div class="mb_20">
                        <h2 class="fs_22 fw_600 mb_14">프롬프트</h2>
                        <input class="custom-input mb_10" type="text" placeholder="프롬프트 제목" />
                        <textarea class="custom-textarea" placeholder="프롬프트 내용"></textarea>
                    </div>
                    <div class="mb_20">
                        <h2 class="fs_22 fw_600 mb_14">변수</h2>
                        <div class="variable-item">
                            <div class="flex-c gap_10 mb_10">
                                <input class="custom-input" type="text" placeholder="변수 이름" />
                                <div class="dropdown" id="variable-type">
                                    <button class="btn-variable-type fs_15 fw_500" type="button" data-toggle="dropdown" aria-expanded="false">
                                        <div class="dropdown-text">변수 타입</div>
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
                            <input class="custom-input" type="text" placeholder="변수 내용" />
                        </div>
                        <button class="btn-add"><img src="../img/plus.svg" alt="추가하기"></button>
                    </div>
                </div>

                <div class="flex-c gap_20">
                    <button class="btn-accept">챗봇 생성</button>
                    <button class="btn-cancel" onclick="window.location.href='./admin_createbot.php'">취소</button>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 변수 타입 드롭다운 이벤트 리스너
            function attachVariableTypeListeners() {
                document.querySelectorAll('.btn-variable-type').forEach(button => {
                    button.addEventListener('click', function(e) {
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
                    });
                });

                document.querySelectorAll('.dropdown-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const dropdown = this.closest('.dropdown');
                        const dropdownText = dropdown.querySelector('.dropdown-text');
                        const button = dropdown.querySelector('button');
                        
                        dropdownText.textContent = this.textContent;
                        dropdown.querySelector('.dropdown-menu').classList.remove('show');
                        button.setAttribute('aria-expanded', 'false');
                    });
                });
            }

            // 초기 드롭다운 이벤트 리스너 연결
            attachVariableTypeListeners();

            // 변수 추가 버튼
            const variableAddBtn = document.querySelector('.mb_20:nth-child(4) .btn-add');
            variableAddBtn.addEventListener('click', function() {
                const newVariable = document.createElement('div');
                newVariable.className = 'variable-item';
                newVariable.innerHTML = `
                    <div class="flex-c gap_10 mb_10">
                        <input class="custom-input" type="text" placeholder="변수 이름" />
                        <div class="dropdown" id="variable-type">
                            <button class="btn-variable-type fs_15 fw_500" type="button" data-toggle="dropdown" aria-expanded="false">
                                <div class="dropdown-text">변수 타입</div>
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
                    <input class="custom-input" type="text" placeholder="변수 내용" />
                `;
                this.insertAdjacentElement('beforebegin', newVariable);
                attachVariableTypeListeners(); // 새로 추가된 드롭다운에 이벤트 리스너 연결
            });

            // 문서 클릭 시 드롭다운 닫기
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                        menu.closest('.dropdown').querySelector('button').setAttribute('aria-expanded', 'false');
                    });
                }
            });

            // 변수 삭제 버튼
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-delete')) {
                    e.preventDefault();
                    const variableItem = e.target.closest('.variable-item');
                    if (variableItem) {
                        if (confirm('정말로 이 변수를 삭제하시겠습니까?')) {
                            variableItem.remove();
                        }
                    }
                }
            });

            // 챗봇 생성 버튼
            document.querySelector('.btn-accept').addEventListener('click', async function() {
                try {
                    // 챗봇 이름 가져오기
                    const botName = document.querySelector('.mb_20:nth-child(1) .custom-input').value.trim();
                    if (!botName) {
                        alert('챗봇 이름을 입력해주세요.');
                        return;
                    }

                    // 카테고리 정보 수집
                    const categoryInput = document.querySelector('input[name="category"]:checked')
                        .closest('label')
                        .querySelector('.custom-input');
                    const categoryName = categoryInput.value.trim();
                    
                    if (!categoryName) {
                        alert('카테고리 이름을 입력해주세요.');
                        return;
                    }

                    // 프롬프트 정보 수집
                    const promptTitle = document.querySelector('.mb_20:nth-child(3) .custom-input').value.trim();
                    const promptContent = document.querySelector('.mb_20:nth-child(3) .custom-textarea').value.trim();

                    if (!promptTitle || !promptContent) {
                        alert('프롬프트 제목과 내용을 입력해주세요.');
                        return;
                    }

                    // 변수 정보 수집
                    const variables = [];
                    document.querySelectorAll('.variable-item').forEach(varItem => {
                        const nameInput = varItem.querySelector('.custom-input');
                        const typeDropdown = varItem.querySelector('.dropdown-text');
                        const descInput = varItem.querySelector('.custom-input:last-child');
                        
                        if (nameInput && typeDropdown && descInput) {
                            const name = nameInput.value.trim();
                            const type = typeDropdown.dataset.value || 'text';
                            const description = descInput.value.trim();

                            if (name && description) {
                                variables.push({
                                    cv_name: name,
                                    cv_type: type,
                                    cv_description: description
                                });
                            }
                        }
                    });

                    if (variables.length === 0) {
                        alert('최소 하나의 변수를 입력해주세요.');
                        return;
                    }

                    // 데이터 전송
                    const response = await fetch('create_chatbot.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            bot_name: botName,
                            categories: [{  // categories 배열로 변경
                                name: categoryName,
                                is_selected: true
                            }],
                            prompt: {
                                cp_title: promptTitle,
                                cp_content: promptContent
                            },
                            variables: variables
                        })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        alert('챗봇이 성공적으로 생성되었습니다.');
                        window.location.href = 'admin_managebot.php';
                    } else {
                        throw new Error(result.message || '챗봇 생성에 실패했습니다.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('오류가 발생했습니다: ' + error.message);
                }
            });
        });
        </script>
    </body>
</html>