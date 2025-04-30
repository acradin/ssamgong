<?php
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head.inc.php";
$chk_menu = '13';
$chk_sub_menu = '1';
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head_menu.inc.php";

// 변수 타입 매핑 정의
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
                    <h4 class="card-title">챗봇 관리</h4>
                    
                    <!-- CSS 스타일 추가 (card-body 시작 전에 추가) -->
                    <style>
                    /* 비활성화된 select 박스의 스타일 */
                    select:disabled {
                        background-color: #e9ecef !important;  /* 회색 배경 */
                        cursor: not-allowed !important;        /* 커서 모양 변경 */
                        opacity: 0.7 !important;               /* 약간 투명하게 */
                        color: #6c757d !important;            /* 텍스트 색상 회색으로 */
                        border-color: #ced4da !important;      /* 테두리 색상 */
                    }

                    /* 비활성화된 select 박스의 옵션 스타일 */
                    select:disabled option {
                        color: #6c757d !important;
                        background-color: #e9ecef !important;
                    }

                    /* 배지 스타일 */
                    .badge {
                        padding: 8px 12px;
                        font-size: 12px;
                        font-weight: 500;
                        border-radius: 4px;
                    }

                    .badge-primary {
                        background-color: #1ba7b4;
                        color: white;
                    }

                    .badge-secondary {
                        background-color: #6c757d;
                        color: white;
                    }

                    /* 변수 아이템 스타일 보완 */
                    .variable-item {
                        border: 1px solid #dee2e6;
                        padding: 15px;
                        border-radius: 4px;
                    }

                    .variable-item:hover {
                        background-color: #f8f9fa;
                    }
                    </style>

                    <!-- 필터 영역 -->
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label class="col-sm-1 col-form-label">카테고리</label>
                                <div class="col-sm-4">
                                    <select class="form-control form-select" id="chatbot" onchange="loadCategories(this.value)">
                                        <option value="">챗봇 선택</option>
                                        <?php
                                        // 챗봇 가져오기
                                        $parent_categories = $DB->rawQuery("SELECT ct_idx, ct_name FROM category_t WHERE parent_idx IS NULL ORDER BY ct_order");
                                        foreach ($parent_categories as $category) {
                                            $selected = (isset($_GET['chatbot']) && $_GET['chatbot'] == $category['ct_idx']) ? 'selected' : '';
                                            echo "<option value='{$category['ct_idx']}' {$selected}>{$category['ct_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <div class="select-wrapper">
                                        <select class="form-control form-select" id="category" disabled>
                                            <option value="">카테고리 선택</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <!-- 메인 컨텐츠 영역 -->
                    <div id="content-area" class="mt-4">
                        <div class="text-muted text-center py-4">
                            <i class="mdi mdi-information-outline mr-1"></i>
                            챗봇을 선택해주세요
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// PHP의 변수 타입 매핑을 JavaScript로 전달
const variableTypeMap = <?php echo json_encode($variableTypeMap); ?>;

// 필터 관련 함수 수정
function loadCategories(parentId) {
    const categorySelect = document.getElementById('category');
    const contentArea = document.getElementById('content-area');
    
    if (!parentId) {
        categorySelect.disabled = true;
        categorySelect.innerHTML = '<option value="">카테고리 선택</option>';
        contentArea.innerHTML = `
            <div class="text-muted text-center py-4">
                <i class="mdi mdi-information-outline mr-1"></i>
                챗봇을 선택해주세요
            </div>
        `;
        return;
    }

    // AJAX 호출에서 status=Y 조건 제거
    fetch(`get_categories.php?parent_idx=${parentId}`)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">카테고리 선택</option>';
            data.forEach(category => {
                const selected = (new URLSearchParams(window.location.search).get('category') == category.ct_idx) ? 'selected' : '';
                options += `<option value="${category.ct_idx}" ${selected}>${category.ct_name}</option>`;
            });
            categorySelect.innerHTML = options;
            categorySelect.disabled = false;

            contentArea.innerHTML = `
                <div class="text-muted text-center py-4">
                    <i class="mdi mdi-information-outline mr-1"></i>
                    카테고리를 선택해주세요
                </div>
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            categorySelect.disabled = true;
            categorySelect.innerHTML = '<option value="">카테고리 선택</option>';
        });
}

// 카테고리 선택 이벤트 리스너 수정
document.getElementById('category').addEventListener('change', function() {
    const chatbotId = document.getElementById('chatbot').value;
    const categoryId = this.value;
    
    if (categoryId) {
        // 선택된 값으로 페이지 새로고침
        const params = new URLSearchParams(window.location.search);
        params.set('chatbot', chatbotId);
        params.set('category', categoryId);
        
        // 카테고리 데이터 로드
        loadCategoryData(chatbotId, categoryId);
    } else {
        // 카테고리가 선택되지 않았을 때는 챗봇 ID만 유지
        window.location.href = `?chatbot=${chatbotId}`;
    }
});

// 카테고리 데이터 로드 함수
function loadCategoryData(chatbotId, categoryId) {
    fetch(`get_category_data.php?category_id=${categoryId}`)
        .then(response => response.json())
        .then(data => {
            const contentArea = document.getElementById('content-area');
            
            if (data.success) {
                const promptTitle = data.prompt?.cp_title || '';
                const promptContent = data.prompt?.cp_content || '';
                const variables = data.variables || [];
                const botDescription = data.bot_description || '';

                contentArea.innerHTML = `
                    <div class="form-group row align-items-center">
                        <label class="col-sm-2 col-form-label">챗봇 이름</label>
                        <div class="col-sm-10">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">${data.parent_category?.ct_name || ''}</h5>
                                <button class="btn ${data.parent_category?.ct_status === 'Y' ? 'btn-info' : 'btn-outline-secondary'}" 
                                        id="parent-status-button" 
                                        data-status="${data.parent_category?.ct_status || ''}"
                                        data-category-id="${data.parent_category?.ct_idx || ''}">
                                    ${data.parent_category?.ct_status === 'Y' ? '활성화' : '비활성화'}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">챗봇 설명</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" rows="3" readonly>${botDescription}</textarea>
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label class="col-sm-2 col-form-label">카테고리</label>
                        <div class="col-sm-10">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">${data.category.ct_name}</h5>
                                <button class="btn ${data.category.ct_status === 'Y' ? 'btn-info' : 'btn-outline-secondary'}" 
                                        id="category-status-button" 
                                        data-status="${data.category.ct_status}"
                                        data-category-id="${data.category.ct_idx}">
                                    ${data.category.ct_status === 'Y' ? '활성화' : '비활성화'}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">프롬프트</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control mb-2" value="${promptTitle}" readonly />
                            <textarea class="form-control" rows="4" readonly>${promptContent}</textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">변수</label>
                        <div class="col-sm-10">
                            ${variables.length > 0 ? 
                                variables.map(variable => `
                                    <div class="variable-item mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <input type="text" class="form-control flex-grow-1 mr-2" value="${variable.cv_name}" readonly />
                                            <select class="form-control mr-2" style="width: auto; min-width: 100px;" disabled>
                                                <option>${variableTypeMap[variable.cv_type] || variable.cv_type}</option>
                                            </select>
                                            <div class="badge ${variable.cv_required === 'Y' ? 'badge-primary' : 'badge-secondary'} mr-2">
                                                ${variable.cv_required === 'Y' ? '필수' : '선택'}
                                            </div>
                                        </div>
                                        ${variable.cv_type === 'select' ? `
                                            <div class="select-options mb-2">
                                                <input type="text" class="form-control" value="${
                                                    JSON.parse(variable.cv_options || '[]')
                                                        .join(', ')
                                                }" readonly />
                                            </div>
                                        ` : ''}
                                        <input type="text" class="form-control" value="${variable.cv_description}" readonly />
                                    </div>
                                `).join('') :
                                '<p class="text-muted text-center">등록된 변수가 없습니다.</p>'
                            }
                        </div>
                    </div>

                    <p class="p-3 text-center">
                        <button class="btn btn-outline-primary" onclick="location.href='./admin_managebot_edit.php?ct_idx=${categoryId}'">수정하기</button>
                        <button class="btn btn-outline-danger mx-2" onclick="deleteCategory(${categoryId})">삭제하기</button>
                    </p>
                `;

                // 상태 버튼 이벤트 리스너 추가
                ['parent-status-button', 'category-status-button'].forEach(buttonId => {
                    const button = document.getElementById(buttonId);
                    if (button) {
                        button.addEventListener('click', function() {
                            const currentStatus = this.dataset.status;
                            const newStatus = currentStatus === 'Y' ? 'N' : 'Y';
                            const categoryId = this.dataset.categoryId;
                            
                            // 상태 업데이트 API 호출
                            fetch('update_category_status.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    category_id: categoryId,
                                    status: newStatus
                                })
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    // 버튼 상태 업데이트
                                    this.dataset.status = newStatus;
                                    this.textContent = newStatus === 'Y' ? '활성화' : '비활성화';
                                    this.className = `btn ${newStatus === 'Y' ? 'btn-info' : 'btn-outline-secondary'}`;
                                } else {
                                    throw new Error(result.error || '상태 변경에 실패했습니다.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('상태 변경에 실패했습니다.');
                            });
                        });
                    }
                });
            } else {
                throw new Error(data.error || '데이터를 불러오는데 실패했습니다');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('content-area').innerHTML = `
                <div class="text-muted text-center py-4">
                    <i class="mdi mdi-information-outline mr-1"></i>
                    ${error.message}
                </div>
            `;
        });
}

// 페이지 로드 시 초기 상태 설정
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const chatbot = params.get('chatbot');
    const category = params.get('category');
    
    if (chatbot) {
        document.getElementById('chatbot').value = chatbot;
        loadCategories(chatbot);
        
        if (category) {
            // URL에 category 파라미터가 있으면 해당 값 선택
            const categorySelect = document.getElementById('category');
            categorySelect.value = category;
        }
    }
});

function deleteCategory(categoryId) {
    $.confirm({
        title: '확인',
        content: '정말 삭제하시겠습니까? 삭제된 데이터는 복구할 수 없습니다.',
        buttons: {
            확인: function () {
                // 카테고리 완전 삭제 처리
                fetch('delete_category.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        category_id: categoryId
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        jalert('삭제되었습니다.', function() {
                            // 카테고리 선택 초기화
                            const chatbotId = document.getElementById('chatbot').value;
                            loadCategories(chatbotId);
                            
                            // 컨텐츠 영역 초기화
                            document.getElementById('content-area').innerHTML = `
                                <div class="text-muted text-center py-4">
                                    <i class="mdi mdi-information-outline mr-1"></i>
                                    카테고리를 선택해주세요
                                </div>
                            `;
                            
                            // select 박스 초기화
                            document.getElementById('category').value = '';
                        });
                    } else {
                        throw new Error(result.error || '삭제에 실패했습니다.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    jalert('삭제에 실패했습니다.');
                });
            },
            취소: function () {
            }
        }
    });
}
</script>

<?php
include $_SERVER['DOCUMENT_ROOT'] . "/mng/foot.inc.php";
?>