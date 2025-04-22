<?php
if ($_SESSION['_mt_level'] < 8 && $_SERVER['PHP_SELF'] != "./login") {
    alert("관리자만 접근할 수 있습니다.", APP_DOMAIN . "/mng/login");
}
?>

<?php if ($chk_webeditor == "Y") { ?>
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" />
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script type="text/javascript" src="<?= CDN_HTTP ?>/js/summernote-ko-KR.js"></script>
<?php } ?>

<div class="container-scroller">
    <!-- 상단바 시작 -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="navbar-brand-wrapper d-flex justify-content-center">
            <div class="navbar-brand-inner-wrapper d-flex justify-content-between align-items-center w-100">
                <a class="navbar-brand brand-logo" href="<?= ADMIN_URL ?>">
                    <h3><img src="<?= CDN_HTTP ?>/img/logo.svg" alt="<?= APP_TITLE ?>" /></h3>
                </a>
                <a class="navbar-brand brand-logo-mini" href="<?= ADMIN_URL ?>">
                    <h3><img src="<?= CDN_HTTP ?>/img/android-chrome-192x192.png" alt="<?= APP_TITLE ?>" /></h3>
                </a>
                <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize"><span class="mdi mdi-sort-variant"></span></button>
            </div>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
            <ul class="navbar-nav navbar-nav-right">
                <li class="nav-item nav-profile dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown"><span class="nav-profile-name"><?= $_SESSION['_mt_name'] ?> 님 반갑습니다.</span></a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                        <a href="../" class="dropdown-item" target="_blank"> <i class="mdi mdi-home text-primary"></i> 홈페이지</a>
                        <a href="<?= ADMIN_URL ?>/logout" class="dropdown-item"> <i class="mdi mdi-logout text-primary"></i> 로그아웃</a>
                    </div>
                </li>
            </ul>
            <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas"> <span class="mdi mdi-menu"></span></button>
        </div>
    </nav>
    <!-- 상단바 끝 -->

    <div class="container-fluid page-body-wrapper">
        <!-- 왼쪽메뉴 시작 -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <ul class="nav">
                <li class="nav-item<?php if ($chk_menu == '') { ?> active<?php } ?>">
                    <a class="nav-link" href="./">
                        <i class="mdi mdi-monitor-dashboard menu-icon"></i>
                        <span class="menu-title">대시보드</span>
                    </a>
                </li>
                <li class="nav-item<?php if ($chk_menu == '1') { ?> active<?php } ?>">
                    <a class="nav-link" data-toggle="collapse" href="#menu2" aria-expanded="<?php if ($chk_menu == '1') { ?>true<?php } else { ?>false<?php } ?>" aria-controls="member">
                        <i class="mdi mdi-account-box-outline menu-icon"></i>
                        <span class="menu-title">회원관리</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse<?php if ($chk_menu == '1') { ?> show<?php } ?>" id="menu2">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '1' && $chk_sub_menu == '1') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/member_list">일반회원</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '1' && $chk_sub_menu == '2') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/faculty_approval_list">교직원 승인관리</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '1' && $chk_sub_menu == '3') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/faculty_member_list">교직원회원</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '1' && $chk_sub_menu == '4') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/member_retire_list">일반회원 탈퇴회원</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '1' && $chk_sub_menu == '5') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/faculty_member_retire_list">교직원회원 탈퇴회원</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item<?php if ($chk_menu == '2') { ?> active <?php } ?>">
                    <a class="nav-link" data-toggle="collapse" href="#menu3" aria-expanded="<?php if ($chk_menu == '2') { ?>true<?php } else { ?>false<?php } ?>" aria-controls="product">
                        <i class="mdi mdi-package-variant-closed menu-icon"></i>
                        <span class="menu-title">자료관리</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse<?php if ($chk_menu == '2') { ?> show<?php } ?>" id="menu3">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '2' && $chk_sub_menu == '2') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/product_list?pct_idx=2">담임</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '2' && $chk_sub_menu == '3') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/product_list?pct_idx=3">교과</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '2' && $chk_sub_menu == '4') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/product_list?pct_idx=4">업무</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '2' && $chk_sub_menu == '5') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/product_list?pct_idx=5">전자책</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '2' && $chk_sub_menu == '1') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/product_ad_list">광고 자료 관리</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item<?php if ($chk_menu == '3') { ?> active<?php } ?>">
                    <a class="nav-link" data-toggle="collapse" href="#menu4" aria-expanded="<?php if ($chk_menu == '3') { ?>true<?php } else { ?>false<?php } ?>" aria-controls="group">
                        <i class="mdi mdi-magnify menu-icon"></i>
                        <span class="menu-title">검색어 관리</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse<?php if ($chk_menu == '3') { ?> show<?php } ?>" id="menu4">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '3' && $chk_sub_menu == '1') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/recommend_keyword_list">추천 키워드</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '3' && $chk_sub_menu == '2') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/recommend_search_list">추천 검색어</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item<?php if ($chk_menu == '4') { ?> active <?php } ?>">
                    <a class="nav-link" href="<?= ADMIN_URL ?>/payment_list"> <i class="mdi mdi-credit-card-outline menu-icon"></i>
                        <span class="menu-title">쌤공 이용권 결제 관리</span>
                    </a>
                </li>

                <li class="nav-item<?php if ($chk_menu == '5') { ?> active <?php } ?>">
                    <a class="nav-link" href="<?= ADMIN_URL ?>/order_list"> <i class="mdi mdi-wallet-membership menu-icon"></i>
                        <span class="menu-title">자료구매 관리</span>
                    </a>
                </li>

                <li class="nav-item<?php if ($chk_menu == '6') { ?> active <?php } ?>">
                    <a class="nav-link" href="<?= ADMIN_URL ?>/payback_list"> <i class="mdi mdi-cash-usd-outline menu-icon"></i>
                        <span class="menu-title">출금 관리</span>
                    </a>
                </li>

                <li class="nav-item<?php if ($chk_menu == '7') { ?> active <?php } ?>">
                    <a class="nav-link" data-toggle="collapse" href="#menu7" aria-expanded="<?php if ($chk_menu == '7') { ?>true<?php } else { ?>false<?php } ?>" aria-controls="community">
                        <i class="mdi mdi-comment-processing-outline menu-icon"></i>
                        <span class="menu-title">커뮤니티 관리</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse<?php if ($chk_menu == '7') { ?> show<?php } ?>" id="menu7">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '7' && $chk_sub_menu == '9') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/community_category_list">커뮤니티 카테고리 관리</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '7' && $chk_sub_menu == '1') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/community_list?cct_idx=1">소통•지혜•정보</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '7' && $chk_sub_menu == '2') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/community_list?cct_idx=2">자료 요청</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '7' && $chk_sub_menu == '3') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/community_list?cct_idx=3">유용한 사이트</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '7' && $chk_sub_menu == '15') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/community_list?cct_idx=15">청원</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '7' && $chk_sub_menu == '4') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/community_list?cct_idx=4">퍼스널브랜딩</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '7' && $chk_sub_menu == '5') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/community_list?cct_idx=5">모임•전학공</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '7' && $chk_sub_menu == '16') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/community_list?cct_idx=16">홍보</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '7' && $chk_sub_menu == '6') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/community_list?cct_idx=6">설문조사</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item<?php if ($chk_menu == '8') { ?> active<?php } ?>">
                    <a class="nav-link" data-toggle="collapse" href="#menu8" aria-expanded="<?php if ($chk_menu == '8') { ?>true<?php } else { ?>false<?php } ?>" aria-controls="group">
                        <i class="mdi mdi-face-agent menu-icon"></i>
                        <span class="menu-title">문의관리</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse<?php if ($chk_menu == '8') { ?> show<?php } ?>" id="menu8">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '8' && $chk_sub_menu == '1') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/qna_list">1:1 문의</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '8' && $chk_sub_menu == '2') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/product_qna_list">자료 문의</a>
                            </li>
                        </ul>
                    </div>
                </li>
                
                <li class="nav-item<?php if ($chk_menu == '9') { ?> active<?php } ?>">
                    <a class="nav-link" data-toggle="collapse" href="#menu9" aria-expanded="<?php if ($chk_menu == '9') { ?>true<?php } else { ?>false<?php } ?>" aria-controls="group">
                        <i class="mdi mdi-bell-outline menu-icon"></i>
                        <span class="menu-title">신고관리</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse<?php if ($chk_menu == '9') { ?> show<?php } ?>" id="menu9">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '9' && $chk_sub_menu == '1') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/declaration_review_list">리뷰 신고</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '9' && $chk_sub_menu == '2') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/declaration_list">게시글 신고</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '9' && $chk_sub_menu == '3') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/declaration_comment_list">댓글 신고</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item<?php if ($chk_menu == '10') { ?> active <?php } ?>">
                    <a class="nav-link" href="<?= ADMIN_URL ?>/review_list"> <i class="mdi mdi-pencil-box-outline menu-icon"></i>
                        <span class="menu-title">후기관리</span>
                    </a>
                </li>

                <li class="nav-item<?php if ($chk_menu == '11') { ?> active <?php } ?>">
                    <a class="nav-link" href="<?= ADMIN_URL ?>/banner_list"> <i class="mdi mdi-blogger menu-icon"></i>
                        <span class="menu-title">배너관리</span>
                    </a>
                </li>

                <li class="nav-item<?php if ($chk_menu == '12') { ?> active<?php } ?>">
                    <a class="nav-link" data-toggle="collapse" href="#menu12" aria-expanded="<?php if ($chk_menu == '12') { ?>true<?php } else { ?>false<?php } ?>" aria-controls="analytics">
                        <i class="mdi mdi-comment-multiple-outline menu-icon"></i>
                        <span class="menu-title">게시판 관리</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse<?php if ($chk_menu == '12') { ?> show<?php } ?>" id="menu12">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '12' && $chk_sub_menu == '1') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/notice_list">공지사항</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '12' && $chk_sub_menu == '2') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/faq_list">FAQ</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '12' && $chk_sub_menu == '3') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/faq_category_list">FAQ 카테고리</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- 업무 자동화 AI -->
                <li class="nav-item<?php if ($chk_menu == '13') { ?> active<?php } ?>">
                    <a class="nav-link" data-toggle="collapse" href="#menu13" aria-expanded="<?php if ($chk_menu == '13') { ?>true<?php } else { ?>false<?php } ?>" aria-controls="chatbot">
                        <i class="mdi mdi-robot menu-icon"></i>
                        <span class="menu-title">챗봇 관리</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse<?php if ($chk_menu == '13') { ?> show<?php } ?>" id="menu13">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '13' && $chk_sub_menu == '1') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/admin_managebot.php">챗봇 관리</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '13' && $chk_sub_menu == '2') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/admin_result.php">결과 조회</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '13' && $chk_sub_menu == '3') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/admin_point.php">포인트 조회</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '13' && $chk_sub_menu == '4') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/admin_createbot.php">챗봇 생성</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item<?php if ($chk_menu == '90') { ?> active<?php } ?>">
                    <a class="nav-link" data-toggle="collapse" href="#menu90" aria-expanded="<?php if ($chk_menu == '90') { ?>true<?php } else { ?>false<?php } ?>" aria-controls="setup">
                        <i class="mdi mdi-settings-outline menu-icon"></i>
                        <span class="menu-title">설정</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse<?php if ($chk_menu == '90') { ?> show<?php } ?>" id="menu90">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '90' && $chk_sub_menu == '1') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/setup_form">기본설정</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '90' && $chk_sub_menu == '2') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/agree_form">약관설정</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($chk_menu == '90' && $chk_sub_menu == '3') { ?> active<?php } ?>" href="<?= ADMIN_URL ?>/account_management">중간 관리자</a>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>
            <script>
                $(".nav-item, .navbar-brand").on('click', function(event) {
                    f_localStorage_reset();
                });
            </script>
        </nav>
        <!-- 왼쪽메뉴 끝 -->

        <div class="main-panel">
