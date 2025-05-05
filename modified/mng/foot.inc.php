<!-- 하단 시작 -->
<footer class="footer">
    <div class="d-sm-flex justify-content-center justify-content-sm-between">
        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © <?=date("Y")?> <?=APP_TITLE?>. All rights reserved.</span>
        <span class="text-muted float-none float-sm-right d-block mt-1 mt-sm-0 text-center">
        <?php if ($chk_menu == '13') { ?>
            NuReal
        <?php } else { ?>
            DMONSTER
        <?php } ?>
        </span>
    </div>
</footer>
<!-- 하단 끝 -->

<?php if ($chk_post_code == "Y") { ?>
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
// 우편번호 찾기 찾기 화면을 넣을 element
function foldDaumPostcode(obj_div) {
    var element_wrap = document.getElementById(obj_div);
    // iframe을 넣은 element를 안보이게 한다.
    element_wrap.style.display = 'none';
}

function DaumPostcode(obj_zip, obj_add1, obj_add2, obj_div) {
    var element_wrap = document.getElementById(obj_div);
    // 현재 scroll 위치를 저장해놓는다.
    var currentScroll = Math.max(document.body.scrollTop, document.documentElement.scrollTop);
    new daum.Postcode({
        oncomplete: function(data) {
            // 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.
            //console.log(data);

            // 각 주소의 노출 규칙에 따라 주소를 조합한다.
            // 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
            var fullAddr = data.address; // 최종 주소 변수
            var extraAddr = ''; // 조합형 주소 변수

            // 기본 주소가 도로명 타입일때 조합한다.
            if (data.addressType === 'R') {
                //법정동명이 있을 경우 추가한다.
                if (data.bname !== '') {
                    extraAddr += data.bname;
                }
                // 건물명이 있을 경우 추가한다.
                if (data.buildingName !== '') {
                    extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                }
                // 조합형주소의 유무에 따라 양쪽에 괄호를 추가하여 최종 주소를 만든다.
                fullAddr += (extraAddr !== '' ? ' (' + extraAddr + ')' : '');
            }

            // 우편번호와 주소 정보를 해당 필드에 넣는다.
            document.getElementById(obj_zip).value = data.zonecode; //5자리 새우편번호 사용
            document.getElementById(obj_add1).value = fullAddr;
            document.getElementById(obj_add2).focus();

            if($("#rlt_lat").length > 0) {
                search_address_2_coordinate(fullAddr);
            }

            // iframe을 넣은 element를 안보이게 한다.
            // (autoClose:false 기능을 이용한다면, 아래 코드를 제거해야 화면에서 사라지지 않는다.)
            element_wrap.style.display = 'none';

            // 우편번호 찾기 화면이 보이기 이전으로 scroll 위치를 되돌린다.
            document.body.scrollTop = currentScroll;
        },
        // 우편번호 찾기 화면 크기가 조정되었을때 실행할 코드를 작성하는 부분. iframe을 넣은 element의 높이값을 조정한다.
        onresize: function(size) {
            element_wrap.style.height = size.height + 'px';
        },
        width: '100%',
        height: '100%'
    }).embed(element_wrap);

    // iframe을 넣은 element를 보이게 한다.
    element_wrap.style.display = 'block';
}
</script>
<?php } ?>

<div class="modal" id="splinner_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="text-center">
                <div class="spinner-border m-5" role="status">
                </div>
            </div>
            <div class="text-center mb-5">
                <span class="text-secondary">처리중입니다. 잠시만 기다려주세요.</span>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-default" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-default-Label" aria-hidden="true">
	<div class="modal-dialog" id="modal-default-size" role="document">
		<div class="modal-content" id="modal-default-content">
			<div style="width:1000px;"></div>
		</div>
	</div>
</div>

<iframe src="about:blank" name="hidden_ifrm" id="hidden_ifrm" style="width:100%;height:100px;display:none;"></iframe>
</body>

</html>
<?php
include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>