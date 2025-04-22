<?php
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head.inc.php";
$chk_menu = '13';
$chk_sub_menu = '3';
include $_SERVER['DOCUMENT_ROOT'] . "/mng/head_menu.inc.php";

try {
    // 페이지네이션 설정
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // 검색 조건
    $whereClause = [];
    $params = [];
    
    // 정렬 조건
    $orderBy = isset($_GET['order']) && $_GET['order'] == 'point' ? 'ph.point_amount DESC' : 'ph.created_at DESC';

    // 날짜 필터
    if (isset($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $whereClause[] = 'ph.created_at >= ? AND ph.created_at < DATE_ADD(?, INTERVAL 1 DAY)';
        $params[] = $_GET['start_date'] . ' 00:00:00';
        $params[] = $_GET['end_date'];
    }

    // 기본 쿼리 작성
    $query = "SELECT ph.*, m.mt_id, m.mt_nickname
              FROM point_history_t ph
              LEFT JOIN member_t m ON ph.mt_idx = m.mt_idx";

    // WHERE 절 추가
    if (!empty($whereClause)) {
        $query .= " WHERE " . implode(' AND ', $whereClause);
    }

    // 정렬과 제한 추가
    $query .= " ORDER BY " . $orderBy;
    
    if (empty($params)) {
        // 파라미터가 없는 경우
        $query .= " LIMIT $offset, $limit";
        $histories = $DB->rawQuery($query);
        
        // 전체 개수 조회
        $total_result = $DB->rawQuery("SELECT COUNT(*) as total FROM point_history_t ph LEFT JOIN member_t m ON ph.mt_idx = m.mt_idx");
    } else {
        // 파라미터가 있는 경우
        $query .= " LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;
        $histories = $DB->rawQuery($query, $params);
        
        // 전체 개수 조회를 위한 쿼리
        $count_query = "SELECT COUNT(*) as total FROM point_history_t ph LEFT JOIN member_t m ON ph.mt_idx = m.mt_idx";
        if (!empty($whereClause)) {
            $count_query .= " WHERE " . implode(' AND ', $whereClause);
        }
        $total_result = $DB->rawQuery($count_query, array_slice($params, 0, -2));
    }

    $total_records = $total_result[0]['total'];
    $total_pages = ceil($total_records / $limit);

} catch (Exception $e) {
    $error_message = $e->getMessage();
    $histories = [];
    $total_pages = 0;
}
?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">포인트 조회</h4>
                    <form method="post" name="frm_list" id="frm_list" onsubmit="return false;">
                        <input type="hidden" name="act" id="act" value="list" />
                        <input type="hidden" name="obj_pg" id="obj_pg" value="1" />
                        <input type="hidden" name="obj_orderby" id="obj_orderby" value="" />
                        <input type="hidden" name="obj_order_desc_asc" id="obj_order_desc_asc" value="1" />

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="form-group row align-items-center mb-0">
                                    <label for="sel_search" class="col-sm-1 col-form-label">조회 순서</label>
                                    <div class="col-sm-4 col-4">
                                        <div class="btn-group" role="group">
                                            <button type="button" onclick="location.href='?order=latest'" 
                                                    class="btn btn-outline-secondary<?= (!isset($_GET['order']) || $_GET['order'] == 'latest') ? ' btn-info text-white' : '' ?>">
                                                최신순
                                            </button>
                                            <button type="button" onclick="location.href='?order=point'" 
                                                    class="btn btn-outline-secondary<?= (isset($_GET['order']) && $_GET['order'] == 'point') ? ' btn-info text-white' : '' ?>">
                                                포인트순
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="form-group row align-items-center mb-0">
                                    <label class="col-sm-1 col-form-label">기간 검색</label>
                                    <div class="col-sm-4">
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm" id="date_range" name="date_range" 
                                                   value="<?= (isset($_GET['start_date']) && isset($_GET['end_date'])) ? $_GET['start_date'] . ' to ' . $_GET['end_date'] : '' ?>" 
                                                   placeholder="기간별 검색" readonly onclick="clearDateIfExists(event)">
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="form-group row align-items-center mb-0">
                                    <div class="col-sm-12 text-center">
                                        <input type="button" class="btn btn-info" value="검색" onclick="f_apply_filters()">
                                        <input type="button" class="btn btn-secondary" value="초기화" onclick="f_reset_filter()">
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </form>

                    <!-- 테이블 영역 -->
                    <div class="table-responsive mt-3">
                        <table class="table inx-table inx-table-card">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center" style="width: 5%">번호</th>
                                    <th class="text-center" style="width: 20%">사용자 정보</th>
                                    <th class="text-center" style="width: 15%">포인트</th>
                                    <th class="text-center" style="width: 15%">유형</th>
                                    <th class="text-center" style="width: 30%">내용</th>
                                    <th class="text-center" style="width: 15%">일시</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($histories)): ?>
                                    <?php foreach ($histories as $history): ?>
                                        <tr>
                                            <td class="text-center"><?= $history['ph_idx'] ?></td>
                                            <td class="text-center">
                                                <p class="mb-0">닉네임: <?= htmlspecialchars($history['mt_nickname']) ?></p>
                                                <small class="text-muted">ID: <?= htmlspecialchars($history['mt_id']) ?></small>
                                            </td>
                                            <td class="text-center <?= $history['point_amount'] < 0 ? 'text-danger' : 'text-success' ?> font-weight-bold">
                                                <?= ($history['point_amount'] < 0 ? '-' : '+') . number_format(abs($history['point_amount'])) ?>
                                            </td>
                                            <td class="text-center"><?= htmlspecialchars($history['point_type']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($history['point_description']) ?></td>
                                            <td class="text-center"><?= date('Y-m-d H:i', strtotime($history['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">포인트 내역이 없습니다.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- 페이지네이션 -->
                    <?php if ($total_pages > 1): ?>
                        <div class="mt-4">
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <?php
                                    $url_params = $_GET;
                                    unset($url_params['page']);
                                    $query_string = http_build_query($url_params);
                                    $query_string = $query_string ? '&' . $query_string : '';
                                    
                                    // 현재 페이지 그룹 계산
                                    $pageGroup = ceil($page/10);
                                    $startPage = ($pageGroup - 1) * 10 + 1;
                                    $endPage = min($startPage + 9, $total_pages);
                                    $prevGroup = $startPage - 10 > 0 ? $startPage - 10 : 1;
                                    $nextGroup = $endPage + 1 <= $total_pages ? $endPage + 1 : $total_pages;
                                    ?>
                                    
                                    <!-- 첫 페이지로 -->
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=1<?= $query_string ?>" title="첫 페이지">
                                                &lt;&lt;
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- 이전 10개 페이지로 -->
                                    <?php if ($startPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $prevGroup . $query_string ?>" title="이전 10페이지">
                                                &lt;
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <!-- 페이지 번호 -->
                                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i . $query_string ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <!-- 다음 10개 페이지로 -->
                                    <?php if ($endPage < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $nextGroup . $query_string ?>" title="다음 10페이지">
                                                &gt;
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <!-- 마지막 페이지로 -->
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $total_pages . $query_string ?>" title="마지막 페이지">
                                                &gt;&gt;
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // flatpickr 초기화
    const datePicker = flatpickr("#date_range", {
        mode: "range",
        locale: "ko",
        dateFormat: "Y-m-d",
        placeholder: "기간별 검색",
        static: true,
        monthSelectorType: "static",
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                // 날짜 범위가 선택완료되면 자동으로 검색 실행
                f_apply_filters();
            }
        }
    });
});

// 필터 적용 함수
function f_apply_filters() {
    const dateRange = document.getElementById('date_range').value;
    let url = new URL(window.location.href);
    
    // 현재 URL의 파라미터 초기화
    url.searchParams.delete('start_date');
    url.searchParams.delete('end_date');
    url.searchParams.delete('page');
    
    // 날짜 범위가 있으면 파라미터 추가
    if (dateRange) {
        const [startDate, endDate] = dateRange.split(' to ');
        url.searchParams.set('start_date', startDate);
        url.searchParams.set('end_date', endDate);
    }
    
    // 정렬 조건 유지
    const currentOrder = url.searchParams.get('order');
    if (currentOrder) {
        url.searchParams.set('order', currentOrder);
    }
    
    // 페이지 이동
    window.location.href = url.toString();
}

// 필터 초기화 함수
function f_reset_filter() {
    window.location.href = window.location.pathname;
}
</script>

<?php
include $_SERVER['DOCUMENT_ROOT'] . "/mng/foot.inc.php";
?>