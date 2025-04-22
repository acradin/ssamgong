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

                <!-- 상품리스트 -->

                <div class="item_box">
                        <style>
                        #ai-container {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    gap: 30px;
    height: 100%;
    min-height: 200px;
} #ai-container ul {
    display: block;
    width: 100%;
    height: 100%;
    min-height: 200px;
} #ai-container ul li {
    width: calc((100% - 60px) / 3);
    margin: 0;
    display: flex;
    flex-direction: column;
    padding: 1.5rem 1.8rem;
    border: 3px solid #1ba7b4;
    border-radius: 24px;
    height: 100%;
    min-height: 200px;
} #ai-container ul li .ai-name {
    font-size: 2.9rem;
    font-weight: 700;
    display: block;
    text-align: center;
    padding-bottom: 1.3rem;
    border-bottom: 3px solid #cccccc;
    margin-bottom: 1rem;
} #ai-container ul li .ai-lore {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    margin: 1.5rem 0;
} #ai-container ul li .ai-lore p {
    font-size: 1.8rem;
    padding: 0 3rem;
    word-break: keep-all;
    word-wrap: break-word;
    text-align: center;
} .btn-ai-link {
    width: 30%;
    margin: 0 auto;
    padding: 1.1rem;
    border-radius: 100px;
    border: 0;
    background-color: #1ba7b4;
    color: #fff;
    font-size: 1.8rem;
                                text-align: center;
}

.ai-row {
    display: flex;
    gap: 30px;
    margin-bottom: 30px;
    width: 100%;
}
                        </style>
                <div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div>
                <div class="card-body">
                    <h3 class="fs_30 fw_700 mb_20">업무 자동화 AI</h3>
                    
                    <div id="ai-container">
                        <ul>
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

                            $total_chatbots = count($chatbots);
                            foreach ($chatbots as $index => $chatbot) {
                                // 새로운 줄 시작
                                if ($index % 3 == 0) {
                                    echo '<div class="ai-row" style="display: flex; gap: 30px; margin-bottom: 30px; width: 100%;">';
                                }
                                ?>
                                <li>
                                    <span class="ai-name"><?= htmlspecialchars($chatbot['ct_name']) ?></span>
                                    <div class="ai-lore">
                                        <p class="fw_500"><?= htmlspecialchars($chatbot['cd_description']) ?></p>
                                                    </div>
                                    <a class="btn-ai-link fw_500" href="./work_automation_ai_variable_form.php?ct_idx=<?= $chatbot['ct_idx'] ?>">바로가기</a>
                                        </li>
                                    <?php
                                // 줄 닫기 (3개 완성되었거나 마지막 항목일 때)
                                if (($index + 1) % 3 == 0 || $index + 1 == $total_chatbots) {
                                    echo '</div>';
                                }
                            }
                            ?>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
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