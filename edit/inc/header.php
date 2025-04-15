<!-- PC 헤더 -->
<?php if ($_GET['hd_pc'] == '1') {?>
        <style>
            #search_select{
                text-align: left;
                width: 100px;
                padding: 10px;
                font-size: 16px;
                border: none;
                border-right: 1px solid #1BA7B4;;
                border-radius: 0px;
                background-color: white;
                color: #333;
                -webkit-appearance: none;
                -moz-appearance: none;
                background-repeat: no-repeat;
                background-position: right 10px center;
                background-size: 15px;
            }
            #search_select:focus {
                border-color: white;
                outline: none;
                border-right: 1px solid #1BA7B4;;
            }
            .sch_ip {
                padding-left: 1rem !important;
            }
        </style>
<div class="hd">
	<div class="hd_top">
		<div class="container pt_27 pb_15 pc-header">
			<div class="flex-bw mb_20">
				<div class="flex-c">
					<a href="./" class="mr_20 logo"><img src="./img/logo.svg"></a>
					<div class="relative">
						<form class="sch_ip align-items-center" method="get" name="frm_search" id="frm_search" action="./search_update">
                            <select id="search_select" name="search_option" style="    margin-left: 5px;">
                                <option value="title" <?php if($_GET['search_option'] == "title") echo "selected";?>>제목</option>
                                <option value="content" <?php if($_GET['search_option'] == "content") echo "selected";?>>내용</option>
                                <option value="author" <?php if($_GET['search_option'] == "author") echo "selected";?>>작성자</option>
                            </select>
							<input type="search" id="searchinput" name="searchinput" class="form-control fs_14 flex-fill border-0" placeholder="검색어를 입력해주세요"  value="<?=$_GET['search']?>">
							<button type="submit" class="btn btn-icon flex-shrink-0"><img src="./img/ico_search.svg" style="width:2rem;"></button>
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
						<?php include_once("./inc/search_word.php");?>
					</div>
				</div>
				<div class="flex-c">
					<!-- 로그인 -->
                    <?php
                    if ($_SESSION['_mt_idx']) {
                        $mem_row = get_member_t_info($_SESSION['_mt_idx']);
                        $mt_img = get_profile_image_url($mem_row['mt_image1']);
                        ?>
					<div class="flex-c">
						<div class="hd_profile">
							<img src="<?=$mt_img?>" alt="<?=$mem_row['mt_nickname']?>" onerror="this.src=\'<?=$ct_no_profile_img_url?>\'" >
						</div>
						<div class="hd-name">
							<div class="flex-c">
								<p class="fw_300 fs_14 mr_10 lh-25"><?=$mem_row['mt_nickname']?></p><!-- 이름 -->
								<img src="./img/ic_ip_select.svg">
							</div>
							<ul>
								<li><a href="./mypage">마이페이지</a></li>
								<li><a href="./logout">로그아웃</a></li>
								<li><a href="./community_faq">고객센터</a></li>
							</ul>
						</div>
						<div class="hb-point">
							<p><?=number_format($mem_row['mt_point'])?>P</p>
						</div>
					</div>
                        <?php }else{ ?>
					<!-- 비로그인 -->
					 <div class="text-gray2">
						<a href="./login">로그인</a>
					</div>
					<div class="mr_20 ml_20">
						<span class="hd_line"></span>
					</div>
					<div class="text-gray2">
						<a href="./agreement">회원가입</a>
					</div>
                        <?php } ?>
				</div>
			</div>
			<div class="flex-bw-end">
				<div class="left-btn">
					<ul class="flex-c hd_top_menu">
						<li>
							<button class="category_btn">
								<img src="./img/category.svg">
							</button>
						</li>
						<li>
							<a href="https://www.ssemgong.blog/8134c529-cab1-433f-85ad-a5d22ea63609" target="_blank"><p class="fw_600 fs_18">소개</p></a>
						</li>
						<li>
							<a href="./item_classroom"><p class="fw_600 fs_18">담임</p></a>
						</li>
						<li>
							<a href="./item_work"><p class="fw_600 fs_18">업무</p></a>
						</li>
                        <li class="subject">
                            <a><p class="fw_600 fs_18">교과</p></a>
                            <div class="subject-box">
                                <a href="./item_middle"><p>중등</p></a>
                                <a href="./item_high"><p>고등</p></a>
                            </div>
                        </li>
						<li>
							<a href="./item_e_book"><p class="fw_600 fs_18">전자책</p></a>
						</li>
						<li>
							<a href="./community_communication"><p class="fw_600 fs_18">커뮤니티</p></a>
						</li>
						<li>
							<a href="./work_automation_ai"><p class="fw_600 fs_18">업무자동화 AI</p></a>
						</li>
					</ul>
				</div>

                <?php
                $cart_cnt='0';
				$app_dnone = '';
				if($_SESSION['_mt_token_id']){
					// $app_dnone = 'd-none';
				}
                if ($_SESSION['_mt_idx']) {
                    $cart_count = $DB->where('mt_idx', $_SESSION['_mt_idx'])->where('ct_status','0')->getone('cart_t','count(*) as cnt');
                    $cart_cnt = $cart_count['cnt'];
                    ?>
                <?php } ?>
                    <div class="right-btn">
                        <ul class="flex-c text-center">
                            <li class="pr_27 <?=$app_dnone?> ">
                                <a href="./license"  class="flex-c">
                                    <img src="./img/cupon.svg">
                                    <p class="fw_300 pl_6">이용권</p>
                                </a>
                            </li>
                            <li class="pr_15">
                                <a href="./cart" class="flex-c">
                                    <img src="./img/cart.svg">
                                    <p class="fw_300 pl_6">장바구니</p>
                                    <?php if($cart_cnt > 0){?>
                                    <span class="cart-number"><?=$cart_cnt?></span>
                                    <?php } ?>
                                </a>
                            </li>
                            <li>
                                <div class="dropdown">
                                    <button class="custom-select2 form-control" type="button" data-toggle="dropdown" aria-expanded="false">
                                        <div class="line1_text">자료등록</div>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="./data_registration?pct_idx=2">담임</a>
                                        <a class="dropdown-item" href="./data_registration?pct_idx=4">업무</a>
                                        <a class="dropdown-item" href="./data_registration?pct_idx=3">교과</a>
                                        <a class="dropdown-item" href="./data_registration?pct_idx=5">전자책</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
			</div>
		</div>
	</div>
	<?php include_once("./inc/hd_category.php");?>
</div>


<?}else{?>
<?}?>