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

<style>
        /* 메인 컨텐츠 */
        .main-wrapper {
            max-width: 1200px;
            background: linear-gradient(to bottom, #f9fdfd, #e6f7f7);
            border-radius: 30px;
            margin: 30px auto;
            padding: 30px 0;
            box-shadow: 0 10px 30px rgba(0, 160, 160, 0.08);
        }

        .main-shadow {
            background: linear-gradient(145deg, #ffffff, #f6fbfc, #e0f5f5);
            border-radius: 30px;
            box-shadow: 0 15px 40px rgba(0, 160, 160, 0.1);
            padding: 40px;
            position: relative;
            overflow: hidden;
            margin: 20px auto;
        }

        .main-shadow::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, #00a0a0, #4db6ac);
        }

        /* 업무 자동화 AI 섹션 */
        .section-title {
            font-size: 36px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 10px;
            color: #000;
            letter-spacing: -1px;
            position: relative;
        }

        .section-title-highlight {
            display: block;
            margin: 0 auto 35px auto;
            width: 180px;
            height: 8px;
            background: linear-gradient(90deg, #ffe066 0%, #fff6b7 100%);
            border-radius: 6px;
            opacity: 0.7;
        }

        .section-title::after {
            display: none;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
            padding: 0 24px; /* 좌우 여백 추가 */
        }

        @media (max-width: 768px) {
            .card-grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.1);
        }

        .card-content {
            padding: 25px;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: linear-gradient(145deg, #ffffff, #f8f8f8);
            transition: all 0.3s ease;
        }

        .card:hover .card-content {
            background: linear-gradient(145deg, #ffffff, #e6f7f7);
            box-shadow: inset 0 0 30px rgba(0, 160, 160, 0.08);
        }

        .card-title {
            font-size: 22px;
            margin-bottom: 15px;
            font-weight: 700;
            color: #333;
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
            letter-spacing: -0.3px;
        }

        .card-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 2px;
            background-color: #00A0A0;
        }

        .card-description {
            font-size: 15px;
            color: #555;
            margin-bottom: 20px;
            flex-grow: 1;
            line-height: 1.6;
        }

        .card-button {
            display: inline-block;
            background-color: #00A0A0;
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            margin-top: auto;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 15px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 160, 160, 0.2);
        }

        .card-button:hover {
            background-color: #008080;
            box-shadow: 0 4px 10px rgba(0, 160, 160, 0.3);
            transform: translateY(-2px);
        }

        /* 카드 배경색 */
        .card:nth-child(1) .card-content {
            background: linear-gradient(145deg, #ffffff 70%, #e6f7f7 100%);
            box-shadow: inset 0 0 20px rgba(0, 160, 160, 0.05);
        }

        .card:nth-child(2) .card-content {
            background: linear-gradient(145deg, #ffffff 70%, #e8f9f9 100%);
            box-shadow: inset 0 0 20px rgba(0, 160, 160, 0.05);
        }

        .card:nth-child(3) .card-content {
            background: linear-gradient(145deg, #ffffff 70%, #eaf8f8 100%);
            box-shadow: inset 0 0 20px rgba(0, 160, 160, 0.05);
        }

        .card:nth-child(4) .card-content {
            background: linear-gradient(145deg, #ffffff 70%, #ecfafa 100%);
            box-shadow: inset 0 0 20px rgba(0, 160, 160, 0.05);
        }

        .card:nth-child(5) .card-content {
            background: linear-gradient(145deg, #ffffff 70%, #eef9f9 100%);
            box-shadow: inset 0 0 20px rgba(0, 160, 160, 0.05);
        }

        .card:nth-child(6) .card-content {
            background: linear-gradient(145deg, #ffffff 70%, #f0fbfb 100%);
            box-shadow: inset 0 0 20px rgba(0, 160, 160, 0.05);
        }

        /* 카드별 호버 효과 */
        .card:nth-child(1):hover .card-content {
            background: linear-gradient(145deg, #ffffff, #e6f7f7);
        }

        .card:nth-child(2):hover .card-content {
            background: linear-gradient(145deg, #ffffff, #e8f9f9);
        }

        .card:nth-child(3):hover .card-content {
            background: linear-gradient(145deg, #ffffff, #eaf8f8);
        }

        .card:nth-child(4):hover .card-content {
            background: linear-gradient(145deg, #ffffff, #ecfafa);
        }

        .card:nth-child(5):hover .card-content {
            background: linear-gradient(145deg, #ffffff, #eef9f9);
        }

        .card:nth-child(6):hover .card-content {
            background: linear-gradient(145deg, #ffffff, #f0fbfb);
        }

        /* 평가계획서 모달 */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .modal-header {
            background: linear-gradient(to right, #00A0A0, #00c2c2);
            padding: 15px 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
        }

        .close-button {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }

        .form-label {
            background-color: #00A0A0;
            color: white;
            width: 150px;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
        }

        .form-input {
            flex: 1;
            padding: 15px;
            border: none;
            outline: none;
            font-size: 14px;
        }

        .usage-info {
            background-color: #f5f5f5;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .usage-count {
            font-size: 24px;
            font-weight: 700;
            color: #00A0A0;
            display: block;
            margin-bottom: 5px;
        }

        .usage-text {
            font-size: 14px;
            color: #666;
        }

        .usage-warning {
            color: #ff5252;
            font-size: 13px;
            display: block;
            margin-top: 5px;
        }

        .usage-bars {
            display: flex;
            gap: 5px;
        }

        .usage-bar {
            width: 6px;
            height: 40px;
            background-color: #00A0A0;
            border-radius: 3px;
        }

        .bar-1 { opacity: 0.2; }
        .bar-2 { opacity: 0.3; }
        .bar-3 { opacity: 0.4; }
        .bar-4 { opacity: 0.5; }
        .bar-5 { opacity: 0.6; }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            padding: 0 20px 20px;
        }

        .submit-button, .cancel-button {
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-button {
            background-color: #00A0A0;
            color: white;
            border: none;
        }

        .submit-button:hover {
            background-color: #008080;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .cancel-button {
            background-color: white;
            color: #00A0A0;
            border: 1px solid #00A0A0;
        }

        .cancel-button:hover {
            background-color: #f5f5f5;
        }
    </style>

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

            <!-- 상단 서브배너 -->

            <?php

            $DB->where('bt_show', 'Y');

            $DB->where('bt_type', '2');

            $DB->orderBy('bt_rank', 'asc');

            $DB->orderBy('bt_idx', 'desc');

            $banner_list = $DB->get('banner_t');

            ?>

            <div class="sub-top-banner relative">

                <div class="swiper">

                    <ul class="swiper-wrapper">

                        <?php

                        foreach ($banner_list AS $key => $banner_row){

                            $banner_pc_img =  get_banner_url($banner_row['bt_file1']);



                            if($banner_row['bt_link1']){

                                $bt_url = $banner_row['bt_link1'];

                                if($banner_row['bt_target1'] == '1'){

                                    $target = ' target="_blank"';

                                }else{

                                    $target = ' target="_self"';

                                }

                            }else{

                                $bt_url = 'javascript:void(0);';

                            }

                            ?>

                            <li class="swiper-slide">

                                <a href="<?=$bt_url?>" <?=$target?>>

                                    <img src="<?=$banner_pc_img?>">

                                </a>

                            </li>

                        <?php } ?>

                    </ul>

                </div>

                <div class="swiper-button-prev banner-prev"><img src="./img/prev.svg"></div>

                <div class="swiper-button-next banner-next"><img src="./img/next.svg"></div>

            </div>

            <!-- 새로운 디자인 적용 -->
            <div class="main-wrapper">
                <div class="main-shadow">
                    <h2 class="section-title">AI 업무자동화</h2>
                    <span class="section-title-highlight"></span>
                    
                    <div class="card-grid">
                        <?php
                        // 활성화된 챗봇 목록 조회
                        $chatbots = $DB->rawQuery("
                            SELECT 
                                c.ct_idx,
                                c.ct_name,
                                cd.cd_description
                            FROM category_t c
                            LEFT JOIN chatbot_description_t cd ON c.ct_idx = cd.ct_idx
                            WHERE c.parent_idx IS NULL 
                            AND c.ct_status = 'Y'
                            ORDER BY c.ct_order ASC
                        ");

                        foreach ($chatbots as $chatbot) {
                        ?>
                            <div class="card">
                                <div class="card-content">
                                    <h3 class="card-title"><?= htmlspecialchars($chatbot['ct_name']) ?></h3>
                                    <p class="card-description"><?= htmlspecialchars($chatbot['cd_description']) ?></p>
                                    <a href="./work_automation_ai_variable_form.php?ct_idx=<?= $chatbot['ct_idx'] ?>" class="card-button">바로가기</a>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var swiper = new Swiper(".sub-top-banner .swiper", {
        slidesPerView: 1,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: ".banner-next",
            prevEl: ".banner-prev",
        },
    });
</script>

<?php

include $_SERVER['DOCUMENT_ROOT'] . "/foot.inc.php";

include $_SERVER['DOCUMENT_ROOT'] . "/tail.inc.php";

?>