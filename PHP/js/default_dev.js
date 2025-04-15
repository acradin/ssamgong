var eng_num = /[^a-zA-Z0-9_-]/g;
var eng_kor = /[^a-zA-Zㄱ-ㅎ가-힣]/g;
var eng_kor_num = /[^a-zA-Zㄱ-ㅎ가-힣0-9]/g;
var num = /[^0-9]/g;
var eng = /[^a-zA-Z]/g;
var kor = /[ㄱ-ㅎ가-힣]/g;
var email = /[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*\.[a-zA-Z]{2,3}$/i;
var emailf = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/;
var password = /^.*(?=.{6,20})(?=.*[0-9])(?=.*[a-zA-Z]).*$/;
var space = /\s/g;

$(document).ready(function () {
    $(document).on("keyup", "input:text[numberOnly]", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/gi, "")
        );
    });
    $(document).on("keyup", "input:text[abcOnly]", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^a-zA-Z0-9]/gi, "")
        );
    });
    $(document).on("keyup", "input:text[datetimeOnly]", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9:\-]/gi, "")
        );
    });
    $(document).on("keyup", "input:text[abcOnlySamll]", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^a-z0-9]/gi, "")
        );
    });
});

function f_preview_image_delete(obj_id, obj_name) {
    var obj_t = obj_name + obj_id;

    if (obj_t) {
        $("#" + obj_t).val("");
        $("#" + obj_t + "_on").val("");
        $("#" + obj_t + "_del").hide();
        // $("#" + obj_t + "_box").css("border", "1px");
        $("#" + obj_t + "_box").html('<div class="rect"></div>');
    }
}

function f_preview_image_selected(e, obj_id, obj_name) {
    var files = e.target.files;
    var filesArr = Array.prototype.slice.call(files);
    console.log(filesArr);
    var obj_t = obj_name + obj_id;

    filesArr.forEach(function (f) {
        if (!f.type.match("image.*")) {
            jalert("확장자는 이미지 확장자만 가능합니다.");
            return;
        }

        if (f.size > 12000000) {
            jalert("업로드는 10메가 이하만 가능합니다.");
            return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            // $("#" + obj_t + "_box").css("border", "none");
            $("#" + obj_t + "_box").html('<div class="rect"><img src="' + e.target.result + '" /></div>');
            $("#" + obj_t + "_del").show();
        };
        reader.readAsDataURL(f);
    });
}
/*
function f_hp_chk() {
    if ($("#srt_tel").val() == "") {
        jalert("연락처를 입력해주세요.", "", $("#srt_tel").focus());
        return false;
    }

    $.post("./step_update.php", { act: "chk_mt_hp", srt_tel: $("#srt_tel").val() }, function (data) {
        if (data == "Y") {
            set_timer();
        }
    });

    return false;
}*/
/*
function set_timer() {
    var time = 119;
    var min = "";
    var sec = "";
    $("#hp_chk_btn").prop("disabled", true);
    $("#hp_chk_btn").css("background-color", "#e9ecef");
    $("#hp_chk_btn").css("border-color", "#e9ecef");
    $("#hp_chk_btn").css("color", "#222222");
    $("#srt_tel").prop("readonly", true);
    $("#srt_tel").css("background-color", "#e9ecef");
    timer = setInterval(function () {
        min = parseInt(time / 60);
        sec = time % 60;
        $("#certi_hp").show();
        document.getElementById("hp_confirm_timer").innerHTML = "인증번호를 발송했습니다. (유효시간 : " + min + ":" + sec + ")";
        time--;
        if (time < -1) {
            jalert("인증번호 유효시간이 만료 되었습니다.", "", "");
            clearInterval(timer);
            $("#certi_hp").hide();
            $("#hp_chk_btn").prop("disabled", false);
            $("#hp_chk_btn").css("background-color", "#F04E5A");
            $("#hp_chk_btn").css("border-color", "#F04E5A");
            $("#hp_chk_btn").css("color", "#ffffff");
            $("#srt_tel").prop("readonly", false);
        }
    }, 1000);
}
*/

function f_hp_confirm() {
    if ($("#hp_confirm").val() == "") {
        jalert("인증번호를 등록해주세요.", "", $("#hp_confirm").focus());
        return false;
    }

    $.post("./step_update.php", { act: "confirm_hp", srt_tel: $("#srt_tel").val(), hp_confirm: $("#hp_confirm").val() }, function (data) {
        if (data == "Y") {
            jalert("인증이 확인되었습니다.", "", "");
            clearInterval(timer);
            $("#srt_tel_chk").val("Y");
            $("#certi_hp").hide();
            $("#hp_confirm").prop("readonly", true);
            $("#srt_tel").prop("readonly", true);
        } else {
            jalert("인증이 확인되지 않습니다. 인증문자를 확인바랍니다.", "", "");
        }
    });

    return false;
}

function page_replace(url) {
    location.replace(url);
}

function page_move(url) {
    location.href = url;
}

function validateMtId(mt_id) {
    //유저의 아이디체크
    var regex = /^[a-zA-Z0-9_]{4,12}$/;
    return regex.test(mt_id);
}

function validateMtPw(value) {
    var regex = /^[a-zA-Z0-9!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]{4,12}$/;
    return regex.test(value);
}

function page_auth_move(mt_rank1) {
    if (mt_rank1.trim() === "") {
        jalert("해당 페이지에 접근하려면 해당페이지에 대한 권한이 필요합니다.", "", "");
    } else {
        $.ajax({
            url: "/ajax.menu_chg.php",
            type: "POST",
            data: {
                mt_rank1: mt_rank1,
            },
            dataType: "json",
            async: true,
            success: function (data) {
                // console.log(data);
                if (data.result) {
                    location.href = data.page_url;
                } else {
                    jalert("개발된 페이지가 없습니다.", "", "");
                }
            },
            error: function (request, status, error) {
                console.log(status);
                console.log(error);
            },
        });
        // page_move("./menu_chg.php?mt_rank="+mt_rank);
    }
}

function page_reload() {
    location.reload();
}

function openFilePicker() {
    var fileInput = $("#file-input");
    fileInput.click();
}

function handleFileSelect(event) {
    var maxFileSize = 90 * 1024 * 1024; // 프론트단 100MB로 설정
    var files = event.target.files; // 선택한 파일들의 목록
    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        if (file.size <= maxFileSize) {
            console.log("파일 이름:", file.name);
            console.log("파일 크기:", file.size);
            console.log("파일 유형:", file.type);
        } else {
            jalert("파일 크기가 제한을 초과했습니다. 최대 파일 크기는 100MB입니다.");
            return false;
        }
        var formData = new FormData();
        for (var i = 0; i < files.length; i++) {
            formData.append("files[]", files[i]);
        }
        $("#loadding").modal("toggle");
        $.ajax({
            url: "/dev/iacuc/file_upload.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                setTimeout(() => {
                    $("#loadding").modal("hide");
                }, 500);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("파일 업로드 실패:", errorThrown);
            },
        });
    }
}

function f_get_box_list_reset(obj_frm = "") {
  if (obj_frm) {
    var obj_frm_t = obj_frm + " ";
  } else {
    var obj_frm_t = "frm_list ";
  }

  $("#" + obj_frm_t)[0].reset();

  f_get_box_list("1");
}

function f_get_box_list(pg = "", tab = "",f="") {
    var form_t = $("#obj_frm").val();
    var obj_frm_t = "#" + form_t + " ";

    if (pg == null || pg == "") {
        var ls_obj_pg = localStorage.getItem(form_t + "_obj_pg");
        if (ls_obj_pg) {
            pg = ls_obj_pg;

            for (let i = 0; i < localStorage.length; i++) {
                let key = localStorage.key(i);
                if (localStorage.getItem(key) && $(obj_frm_t + "#" + key).val() == "") {
                    // $(obj_frm_t + "#" + key).val(localStorage.getItem(key));
                }
            }
        } else {
            pg = 1;
        }
    }
    $(obj_frm_t + "#obj_pg").val(parseInt(pg));

    var form_t = $("#" + form_t)[0];
    var formData_t = new FormData(form_t);

    if (tab) {
        formData_t.append("sel_tab", tab);
    }

    $.ajax({
        url: $("#obj_uri").val(),
        enctype: "multipart/form-data",
        data: formData_t,
        type: "POST",
        async: true,
        contentType: false,
        processData: false,
        cache: true,
        timeout: 5000,
        success: function (data) {
            if (data) {
                if(tab){
                    $(`#myreview_list_box_${tab}`).html(data);
                }else{
                    for (const [key, value] of formData_t.entries()) {
                        localStorage.setItem(key, value);
                    }

                    $("#" + $(obj_frm_t + "#obj_list").val()).html(data);
                    /*if(f != "") {
                        f();
                    }*/
                    // 콜백 함수 호출
                    if (typeof f === "function") {
                        f();
                    }

                    // 데이터 로드 후 버튼 표시 여부 결정
                    $('.detail_info').each(function() {
                        var contentDiv = $(this).find('.commu-list-content');
                        var moreButton = $(this).find('.detail_info_more_box button');

                        if (contentDiv.prop('scrollHeight') > 500) {
                            // moreButton.show(); // 더보기 버튼 보이기
                        } else {
                            // moreButton.hide(); // 더보기 버튼 숨기기
                            // $(this).addClass('expanded'); // expanded 클래스 추가
                            // contentDiv.css("margin-bottom", "3.0rem"); // 마진 설정
                        }
                    });
                }
            }
        },
        error: function (err) {
            console.log(err);
        },
    });

    return false;
}

function f_get_box_list2(pg = "", tab = "",f="") {
    var form_t = $("#obj_frm2").val();
    var obj_frm_t = "#" + form_t + " ";

    if (pg == null || pg == "") {
        var ls_obj_pg = localStorage.getItem(form_t + "_obj_pg2");
        if (ls_obj_pg) {
            pg = ls_obj_pg;

            for (let i = 0; i < localStorage.length; i++) {
                let key = localStorage.key(i);
                if (localStorage.getItem(key) && $(obj_frm_t + "#" + key).val() == "") {
                    // $(obj_frm_t + "#" + key).val(localStorage.getItem(key));
                }
            }
        } else {
            pg = 1;
        }
    }
    $(obj_frm_t + "#obj_pg2").val(parseInt(pg));

    var form_t = $("#" + form_t)[0];
    var formData_t = new FormData(form_t);

    if (tab) {
        formData_t.append("sel_tab", tab);
    }

    $.ajax({
        url: $("#obj_uri2").val(),
        enctype: "multipart/form-data",
        data: formData_t,
        type: "POST",
        async: true,
        contentType: false,
        processData: false,
        cache: true,
        timeout: 5000,
        success: function (data) {
            if (data) {
                if(tab){
                    $(`#myreview_list_box_${tab}`).html(data);
                }else{
                    for (const [key, value] of formData_t.entries()) {
                        localStorage.setItem(key, value);
                    }

                    $("#" + $(obj_frm_t + "#obj_list2").val()).html(data);
                    if(f != "") {
                        f();
                    }
                }
            }
        },
        error: function (err) {
            console.log(err);
        },
    });

    return false;
}

function f_get_box_list_frame(pg = "", tab = "",f="") {
    var form_t = $("#obj_frm").val();
    var obj_frm_t = "#" + form_t + " ";

    if (pg == null || pg == "") {
        var ls_obj_pg = localStorage.getItem(form_t + "_obj_pg");
        if (ls_obj_pg) {
            pg = ls_obj_pg;

            for (let i = 0; i < localStorage.length; i++) {
                let key = localStorage.key(i);
                if (localStorage.getItem(key) && $(obj_frm_t + "#" + key).val() == "") {
                    // $(obj_frm_t + "#" + key).val(localStorage.getItem(key));
                }
            }
        } else {
            pg = 1;
        }
    }
    $(obj_frm_t + "#obj_pg").val(parseInt(pg));

    var form_t = $("#" + form_t)[0];
    var formData_t = new FormData(form_t);

    if (tab) {
        formData_t.append("sel_tab", tab);
    }

    $.ajax({
        url: $("#obj_uri").val(),
        enctype: "multipart/form-data",
        data: formData_t,
        type: "POST",
        async: true,
        contentType: false,
        processData: false,
        cache: true,
        timeout: 5000,
        success: function (data) {
            if (data) {
                if(tab){
                    $(`#myreview_list_box_${tab}`).html(data);
                }else{
                    for (const [key, value] of formData_t.entries()) {
                        localStorage.setItem(key, value);
                    }

                    $("#" + $(obj_frm_t + "#obj_list").val()).html(data);
                    if(f != "") {
                        f();
                    }
                }
            }
        },
        error: function (err) {
            console.log(err);
        },
    });

    return false;
}
function f_show_chg(u, i, v) {
    $.confirm({
        title: "변경",
        content: "정보를 변경하시겠습니까?",
        buttons: {
            confirm: {
                text: "확인",
                action: function () {
                    $.post(
                        u,
                        {
                            obj_act: "show_chg",
                            idx: i,
                            show_v: v,
                        },
                        function (data) {
                            if (data == "Y") {
                                jalert("변경되었습니다.");
                            }
                        }
                    );
                },
            },
            cancel: {
                btnClass: "btn-outline-default",
                text: "취소",
                action: function () {
                    close();
                },
            },
        },
    });
}

function f_post_del(u, i, o = "") {
    if (o) {
        var o_t = o;
    } else {
        var o_t = "delete";
    }

    $.confirm({
        title: "경고",
        content: "정말 삭제하시겠습니까? 삭제된 자료는 복구되지 않습니다.",
        buttons: {
            confirm: {
                text: "확인",
                action: function () {
                    $.post(
                        u,
                        {
                            obj_act: o_t,
                            idx: i,
                        },
                        function (data) {
                            if (data == "Y") {
                                jalert_url("삭제되었습니다.", "reload");
                            }
                        }
                    );
                },
            },
            cancel: {
                btnClass: "btn-outline-default",
                text: "취소",
                action: function () {
                    close();
                },
            },
        },
    });

    return false;
}

function sendfile_summernote(ctype, file, no, editor) {
    if (!file.type.match("image.*")) {
        jalert("확장자는 이미지 확장자만 가능합니다.");
        return;
    }

    if (file.size > 12000000) {
        jalert("업로드는 10메가 이하만 가능합니다.");
        return;
    }

    var form_data = new FormData();
    form_data.append("act", "upload");
    form_data.append("ctype", ctype);
    form_data.append("file_no", no);
    form_data.append("file", file);
    $.ajax({
        data: form_data,
        type: "POST",
        enctype: "multipart/form-data",
        url: "/mng/sendfile_summernote.php",
        cache: false,
        timeout: 5000,
        contentType: false,
        processData: false,
        success: function (data) {
            var obj = JSON.parse(data);
            $(editor).summernote("insertImage", obj.url);
        },
        error: function (err) {
            console.log(err);
        },
    });
}

function bytesToSize(x) {
    const units = ["bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];

    let l = 0,
        n = parseInt(x, 10) || 0;

    while (n >= 1024 && ++l) {
        n = n / 1024;
    }

    return n.toFixed(n < 10 && l > 0 ? 1 : 0) + " " + units[l];
}

function f_file_btn_upload(obj_name, o = "") {
    var file_up_max_t = parseInt($("#file_up_num").val());
    var obj_id = $("#file_cnt").val();
    if (obj_id < 1) {
        obj_id = 1;
    }
    if (o == "") {
        var obj_t = obj_name + obj_id;
    } else {
        var obj_t = obj_name + o;
    }

    if (obj_id > file_up_max_t) {
        jalert("업로드는 " + file_up_max_t + "개 이하만 가능합니다.");
        return;
    } else {
        var ww = 1;
        $(".lt_file_vi").each(function () {
            if ($(this).val() == "") {
                obj_id = ww;
                return false;
            }
            ww++;
        });

        $("#" + obj_t).click();
    }

    return false;
}

function f_file_box_reset(o = "") {
    if (o == "") {
        $("#file_cnt").val(0);
        $("#file_up_box").html('<li><div><div class="under"><i class="xi xi-diskette fc_aaa mr-2"></i>파일을 업로드해주세요</div></div></li>');
        $("#file_cnt_t").html("");
        $("#btn_file_delete").hide();
    } else {
        $("#file_cnt").val(0);
        $("#" + o).html('<li><div><div class="under"><i class="xi xi-diskette fc_aaa mr-2"></i>파일을 업로드해주세요</div></div></li>');
        $("#file_cnt_t").html("");
        $("#btn_file_delete").hide();
    }

    return false;
}

/*function f_preview_file_selected(e, obj_name, o = "") {
    var files = e.target.files;
    var filesArr = Array.prototype.slice.call(files);
    var obj_id = $("#file_cnt").val();
    if (obj_id < 1) {
        obj_id = 1;
    }
    var file_up_max_t = parseInt($("#file_up_num").val()) + 1;
    if (o == "") {
        var box_t = "file_up_box";
        var obj_t = obj_name + obj_id;
    } else {
        var box_t = o;
        var obj_t = obj_name;
        obj_id = 1;
    }

    if (obj_id >= file_up_max_t) {
        jalert("업로드는 " + file_up_max_t + "개 이하만 가능합니다.");
        return;
    } else {
        filesArr.forEach(function (f) {
            if (f.size > $("#file_up_size").val()) {
                jalert("업로드는 " + bytesToSize($("#file_up_size").val()) + " 이하만 가능합니다.");

                $("#" + obj_t).val("");
                $("#" + obj_t + "_on").val("");
                return false;
            }

            if (obj_id == 1) {
                $("#" + box_t).html("");
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                $("#" + box_t).append(
                    '<li id="' +
                        obj_name +
                        obj_id +
                        '_li"><div> <a class="under"><i class="xi xi-diskette fc_aaa mr-2"></i><u>' +
                        f.name +
                        '</u></a><p class="mt-2 fc_666"><span class="mr-3"><span class="mr-2">파일크기</span> <span>' +
                        bytesToSize(e.loaded) +
                        '</span></span></p></div><div><button type="button" class="btn btn_icon"><img src="/img/del_btn.png" style="width:3.2rem;" onclick="f_preview_file_delete(\'' +
                        obj_id +
                        "', '" +
                        obj_name +
                        "', '" +
                        o +
                        "')\"></button> </div></li>"
                );
                $("#btn_file_delete").show();
                $("#" + obj_t + "_ori").val(f.name);
            };
            reader.readAsDataURL(f);

            $("#file_cnt").val(parseInt(obj_id) + 1);
            $("#file_cnt_t").html(" (" + parseInt(obj_id) + ")");
        });
    }
}*/
/*
function f_preview_file_selected2(e, obj_name, o = "") {
    var files = e.target.files;
    var filesArr = Array.prototype.slice.call(files);
    var obj_id = $("#file_cnt").val();
    if (obj_id < 1) {
        obj_id = 1;
    }
    var file_up_max_t = parseInt($("#file_up_num").val()) + 1;
    if (o == "") {
        var box_t = "file_up_box";
        var obj_t = obj_name + obj_id;
    } else {
        var box_t = o;
        var obj_t = obj_name;
        obj_id = 1;
    }

    if (obj_id >= file_up_max_t) {
        jalert("업로드는 " + file_up_max_t + "개 이하만 가능합니다.");
        return;
    } else {
        filesArr.forEach(function (f) {
            if (f.size > $("#file_up_size").val()) {
                jalert("업로드는 " + bytesToSize($("#file_up_size").val()) + " 이하만 가능합니다.");

                $("#" + obj_t).val("");
                $("#" + obj_t + "_on").val("");
                return false;
            }

            if (obj_id == 1) {
                $("#" + box_t).html("");
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                $("#" + box_t).append(
                    '<li id="' +
                        obj_name +
                        obj_id +
                        '_li"><div class="d-flex align-items-center flex-wrap"><input type="text" class="form-control ml-0" name="it_file' +
                        obj_id +
                        '_ori" id="it_file' +
                        obj_id +
                        '_ori" placeholder="제출 서류명 직접 입력" value="' +
                        f.name +
                        '" style="height:4.2rem; max-width:21rem;"><p class="mt-2 fc_666 pl-0"><i class="xi xi-diskette fc_aaa mr-2"></i> <span class="mr-3"><span class="mr-2">파일크기</span> <span>' +
                        bytesToSize(e.loaded) +
                        '</span></span></p></div><button type="button" class="btn btn_icon"><img src="/img/del_btn.png" style="width:3.2rem;" onclick="f_preview_file_delete(\'' +
                        obj_id +
                        "', '" +
                        obj_name +
                        "', '" +
                        o +
                        "')\"></button> </div></li>"
                );
                $("#btn_file_delete").show();
                $("#" + obj_t + "_ori").val(f.name);
            };
            reader.readAsDataURL(f);

            $("#file_cnt").val(parseInt(obj_id) + 1);
            $("#file_cnt_t").html(" (" + parseInt(obj_id) + ")");
        });
    }
}
*/
/*function f_preview_file_delete(obj_id, obj_name, o = "") {
    if (o == "") {
        var obj_t = obj_name + obj_id;
    } else {
        var obj_t = obj_name;
    }
    var file_cnt_t = parseInt($("#file_cnt").val());

    if (obj_t) {
        $("#" + obj_t).val("");
        $("#" + obj_t + "_on").val("");
        $("#" + obj_t + "_ori").val("");
        $("#" + obj_t + "_li").remove();

        if (o == "") {
            if (file_cnt_t == 2) {
                f_file_box_reset();
            } else {
                $("#file_cnt").val(file_cnt_t - 1);
                $("#file_cnt_t").html(" (" + (file_cnt_t - 2) + ")");
            }
        } else {
            f_file_box_reset(o);
        }
    }
}*/
function f_preview_file_selected(e, obj_id, obj_name) {
    var files = e.target.files;
    var filesArr = Array.prototype.slice.call(files);
    var obj_t = obj_name + obj_id;

    filesArr.forEach(function (f) {
        if (f.size > 1073741824) { // 1GB = 1,073,741,824 bytes
            alert("업로드는 1기가 이하만 가능합니다.");
            return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            $("#" + obj_t + "_box").show();
            $("#" + obj_t + "_box").html('<p>' + f.name + ' </p><button onclick="removeFile(\'' + obj_id + "', '" + obj_name + '\');"><label id="'+obj_name+ obj_id+'_del"><img src="./img/g-delete.svg" alt=""></label></button>');
            $("#" + obj_t + "_rep_box").show();
            $("#" + obj_t + "_rep_text").html(f.name);
            $("#" + obj_t + "_del").show();
            $("#" + obj_t + "_rep_box").show();
        };
        reader.readAsDataURL(f);
    });
}
function f_preview_file_selected2(e, obj_id, obj_name) {
    var files = e.target.files;
    var filesArr = Array.prototype.slice.call(files);
    var obj_t = obj_name + obj_id;

    filesArr.forEach(function (f) {
        if (f.size > 573741824) { // 1GB = 1,073,741,824 bytes
            alert("업로드는 500MB 이하만 가능합니다.");
            return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            $("#" + obj_t + "_box").show();
            $("#" + obj_t + "_box").html('<p>' + f.name + ' </p><button onclick="f_preview_file_delete(\'' + obj_id + "', '" + obj_name + '\');"><label id="'+obj_name+ obj_id+'_del"><img src="./img/g-delete.svg" alt=""></label></button>');
            $("#" + obj_t + "_rep_box").show();
            $("#" + obj_t + "_rep_text").html(f.name);
            $("#" + obj_t + "_del").show();
            $("#" + obj_t + "_rep_box").show();
        };
        reader.readAsDataURL(f);
    });
}

function f_preview_file_delete(obj_id, obj_name) {
    var obj_t = obj_name + obj_id;

    if (obj_t) {
        $("#" + obj_t).val("");
        $("#" + obj_t + "_on").val("");
        $("#" + obj_t + "_del").hide();
        $("#" + obj_t + "_box").html("");
        $("#" + obj_t + "_box").hide();
        $("#" + obj_t + "_rep_text").html("");
        $("#" + obj_t + "_rep_box").hide();
    }
}
function f_preview_file_delete_all(obj_name) {
    if (obj_name) {
        $("." + obj_name + "_v").val("");
        $("." + obj_name + "_vo").val("");
        $("." + obj_name + "_vi").val("");
        $("." + obj_name + "_vs").val("");
        f_file_box_reset();
    }
}

function gourln(url) {
    if (url != "") {
        window.smapAndroid.openUrlBlank(url);
    }
}

function gourl(url) {
    if (url != "") window.open(url);
}

function f_preview_one_image_delete(obj_id, obj_name) {
    var obj_t = obj_name + obj_id;

    if (obj_t) {
        $("#" + obj_t).val("");
        $("#" + obj_t + "_on").val("");
        $("#" + obj_t + "_del").hide();
        $("#" + obj_t + "_box").css("border", "1px dashed #ddd");
        $("#" + obj_t + "_box").html('<i class="xi xi-camera-o"></i>');
    }
}

function f_preview_one_image_selected(e, obj_id, obj_name, fs = "10485760") {
    var files = e.target.files;
    var filesArr = Array.prototype.slice.call(files);
    var obj_t = obj_name + obj_id;
    var file_up_max_t = bytesToSize(parseInt(fs));

    filesArr.forEach(function (f) {
        if (!f.type.match("image.*")) {
            jalert("확장자는 이미지 확장자만 가능합니다.");
            return;
        }

        if (f.size > fs) {
            jalert("업로드는 " + file_up_max_t + " 이하만 가능합니다.");
            return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            $("#" + obj_t + "_box").css("border", "none");
            $("#" + obj_t + "_box").html('<img src="' + e.target.result + '" />');
            $("#" + obj_t + "_del").show();
        };
        reader.readAsDataURL(f);
    });
}

function get_2_year_month(v1, v2) {
    var date1 = new Date(v1);
    var date2 = new Date(v2);

    var elapsedMSec = date2.getTime() - date1.getTime();
    var elapsedDay = elapsedMSec / 1000 / 60 / 60 / 24;
    var elapsedMonth = elapsedMSec / 1000 / 60 / 60 / 24 / 30;

    second = Math.floor(elapsedMSec / 1000);
    minute = Math.floor(second / 60);
    second = second % 60;
    hour = Math.floor(minute / 60);
    minute = minute % 60;
    day = Math.floor(hour / 24);
    hour = hour % 24;
    month = Math.floor(day / 30);
    day = day % 30;
    year = Math.floor(month / 12);
    month = month % 12;

    // console.log(year + "|" + month + "|" + day);

    if (year) {
        var rtn = year + "년 " + month + "개월 " + day + "일";
    } else {
        if (month > 1) {
            var rtn = month + "개월 " + day + "일";
        } else {
            var rtn = day + "일";
        }
    }

    return rtn;
}

function get_date_t(d) {
    var date = new Date(d);

    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    var d = date.getDate();
    var w = "일월화수목금토".charAt(date.getUTCDay());

    // console.log(y+"."+m+"."+d+" ("+w+")");
    return y + "." + m + "." + d + " (" + w + ")";
}

function checkAllToggle(all_selector, check_selector) {
    let el_all_check = document.querySelector(all_selector);
    let el_check_all = document.querySelectorAll(check_selector);
    let is_check = el_all_check.checked;

    if (is_check === true) {
        el_check_all.forEach((checkbox) => {
            if (checkbox.disabled !== true) {
                checkbox.setAttribute("checked", "checked");
                checkbox.checked = true;
            }
        });
    } else {
        el_check_all.forEach((checkbox) => {
            checkbox.removeAttribute("checked", "checked");
            checkbox.checked = false;
        });
    }
}

function checkBoxToggle(all_selector, check_selector) {
    let el_all_check = document.querySelector(all_selector);
    let checkbox_ln = document.querySelectorAll(check_selector + ":enabled").length;
    let check_ln = document.querySelectorAll(check_selector + ":checked:enabled").length;
    if (checkbox_ln === check_ln) {
        el_all_check.setAttribute("checked", "checked");
        el_all_check.checked = true;
    } else {
        el_all_check.removeAttribute("checked", "checked");
        el_all_check.checked = false;
    }
}

function checkBoxToggleEvent(all_selector, check_selector) {
    let el_all_check = document.querySelector(all_selector);
    el_all_check.addEventListener("change", function () {
        checkAllToggle(all_selector, check_selector);
    });

    let el_check_all = document.querySelectorAll(check_selector);
    el_check_all.forEach((el_check, idx) => {
        el_check.addEventListener("change", function () {
            checkBoxToggle(all_selector, check_selector);
        });
    });
}

function comma_num(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function setCookie(cName, cValue, cDay) {
    var expire = new Date();
    expire.setDate(expire.getDate() + cDay);
    cookies = cName + "=" + escape(cValue) + "; path=/ ";
    if (typeof cDay != "undefined") cookies += ";expires=" + expire.toGMTString() + ";";
    document.cookie = cookies;
}

function getCookie(cName) {
    cName = cName + "=";
    var cookieData = document.cookie;
    var start = cookieData.indexOf(cName);
    var cValue = "";
    if (start != -1) {
        start += cName.length;
        var end = cookieData.indexOf(";", start);
        if (end == -1) end = cookieData.length;
        cValue = cookieData.substring(start, end);
    }
    return unescape(cValue);
}

function f_checkbox_all(obj, name) {
    if ($(obj).prop("checked") == true) {
        $('input:checkbox[name="' + name + '[]"]').each(function () {
            $(this).prop("checked", true);
        });
    } else {
        $('input:checkbox[name="' + name + '[]"]').each(function () {
            $(this).prop("checked", false);
        });
    }

    return false;
}

function f_checkbox_each(name) {
    var count = 0;
    $('input:checkbox[name="' + name + '[]"]').each(function () {
        if ($(this).prop("checked") == false) {
            count++;
        }
    });

    if (count == 0) {
        $("#" + name + "_all").prop("checked", true);
    } else {
        $("#" + name + "_all").prop("checked", false);
    }

    return false;
}

function f_member_file_upload_done() {
    $("#camera_album").modal("hide");

    var form_data = new FormData();
    form_data.append("act", "member_profile_get");
    $.ajax({
        data: form_data,
        type: "POST",
        enctype: "multipart/form-data",
        url: "./form_update",
        cache: false,
        timeout: 5000,
        contentType: false,
        processData: false,
        success: function (data) {
            if (data) {
                $("#member_profile_img").attr("src", data);
            }
        },
        error: function (err) {
            console.log(err);
        },
    });
}

function dateFormat(date) {
    let dateFormat2 = date.getFullYear() + "-" + (date.getMonth() + 1 < 9 ? "0" + (date.getMonth() + 1) : date.getMonth() + 1) + "-" + (date.getDate() < 9 ? "0" + date.getDate() : date.getDate());
    return dateFormat2;
}

function f_calendar_init(t = "") {
    var form_data = new FormData();
    var week_chk = $("#week_calendar").val();

    if (t == "today") {
        var cday = new Date();
    } else {
        var sdate = $("#csdate").val();
        var cday = new Date(sdate);
    }

    form_data.append("act", "calendar_list");
    form_data.append("week_chk", week_chk);

    if (week_chk == "N") {
        if (t == "prev") {
            cday.setMonth(cday.getMonth() - 1);
            form_data.append("sdate", dateFormat(cday));
        } else if (t == "next") {
            cday.setMonth(cday.getMonth() + 1);
            form_data.append("sdate", dateFormat(cday));
        } else if (t == "today") {
            form_data.append("sdate", dateFormat(cday));
        } else {
            form_data.append("sdate", dateFormat(cday));
        }

        var cday2 = cday.getFullYear() + "-" + (cday.getMonth() + 1 < 9 ? "0" + (cday.getMonth() + 1) : cday.getMonth() + 1) + "-02";
        $("#csdate").val(cday2);
    } else {
        if (t == "prev") {
            cday.setDate(cday.getDate() - 7);
            form_data.append("sdate", dateFormat(cday));
        } else if (t == "next") {
            cday.setDate(cday.getDate() + 7);
            form_data.append("sdate", dateFormat(cday));
        } else if (t == "today") {
            form_data.append("sdate", dateFormat(cday));
        } else {
            form_data.append("sdate", dateFormat(cday));
        }
        $("#csdate").val(dateFormat(cday));
    }

    setTimeout(() => {
        $("#calendar_date_title").html(cday.getFullYear() + "년 " + (cday.getMonth() + 1) + "월");
    }, 100);

    $.ajax({
        url: "./schedule_update",
        enctype: "multipart/form-data",
        data: form_data,
        type: "POST",
        async: true,
        contentType: false,
        processData: false,
        cache: true,
        timeout: 5000,
        success: function (data) {
            if (data) {
                $("#schedule_calandar_box").html(data);
            }
        },
        error: function (err) {
            console.log(err);
        },
    });
}

function get_pad(v) {
    return v > 9 ? v : "0" + String(v);
}

function maxLengthCheck(object) {
    if (object.value.length > object.maxLength) {
        object.value = object.value.slice(0, object.maxLength);
    }
}

function f_mt_id_chk() {
    if ($("#mt_id").val() == "") {
        $("#mt_id").focus();
        jalert("아이디를 입력해주세요.");
        return false;
    }
    if ($("#mt_id").val().length < 5 || $("#mt_id").val().length > 16) {
        jalert("아이디를 5자리이상 16자리 이하로 입력바랍니다.");
        return false;
    }

    $.post(
        "./sign_update.php",
        {
            act: "chk_mt_id",
            mt_id: $("#mt_id").val(),
        },
        function (data) {
            if (data == "Y") {
                $("#mt_id").prop("readonly", true);
                $("#mt_id_chk_btn").prop("disabled", true);
                $("#mt_id_chk_btn").removeClass("btn-primary");
                $("#mt_id_chk_btn").addClass("btn-secondary");
                $("#mt_id_chk").val("Y");
                jalert("사용할 수 있는 아이디입니다.");
            } else {
                $("#mt_id_chk").val("N");
                jalert("중복된 아이디가 존재합니다.");
            }
        }
    );

    return false;
}

function f_mt_nickname_chk() {
    if ($("#mt_nickname").val() == "") {
        $("#mt_nickname").focus();
        jalert("닉네임을 입력해주세요.");
        return false;
    }
    if ($("#mt_nickname").val().length < 2 || $("#mt_nickname").val().length > 16) {
        jalert("닉네임을 2자리이상 16자리 이하로 입력바랍니다.");
        return false;
    }

    $.post(
        "./join_update.php",
        {
            act: "chk_mt_nickname",
            mt_nickname: $("#mt_nickname").val(),
        },
        function (data) {
            if (data == "Y") {
                $("#mt_nickname").prop("readonly", true);
                $("#mt_nickname_chk_btn").prop("disabled", true);
                $("#mt_nickname_chk_btn").removeClass("btn-primary");
                $("#mt_nickname_chk_btn").addClass("btn-secondary");
                $("#mt_nickname_chk").val("Y");
                jalert("사용할 수 있는 닉네임입니다.");
            } else {
                $("#mt_id_chk").val("N");
                jalert("중복된 닉네임이 존재합니다.");
            }
        }
    );

    return false;
}

function f_m_hp_chk() {
    if ($("#mt_hp").val() == "") {
        $("#mt_hp").focus();
        jalert("휴대전화번호를 등록해주세요.");
        return false;
    }

    $.post(
        "./join_update.php",
        {
            act: "chk_mt_hp",
            mt_hp: $("#mt_hp").val(),
        },
        function (data) {
            if (data == "Y") {
                set_timer();
            }else if(data == "N"){
                jalert("이미 등록된 휴대폰 번호 입니다.");
                return;
            }
        }
    );

    return false;
}

function f_hp_chk() {
    if ($("#mt_hp").val() == "") {
        $("#mt_hp").focus();
        jalert("휴대전화번호를 등록해주세요.");
        return false;
    }

    $.post(
        "./join_update.php",
        {
            act: "find_mt_hp",
            mt_hp: $("#mt_hp").val(),
        },
        function (data) {
            if (data == "Y") {
                set_timer();
            }else if(data == "N"){
                jalert("해당번호로 등록된 정보가 없습니다.");
                return;
            }
        }
    );
    return false;
}

function set_timer() {
    var time = 180;
    var min = "";
    var sec = "";
    $("#m_hp_chk_btn").prop("disabled", true);
    $("#m_hp_confirm_btn").prop("disabled", false);
    $("#m_hp_chk_btn").removeClass("btn-outline-primary");
    $("#m_hp_chk_btn").addClass("btn-secondary");
    $("#mt_hp").prop("readonly", true);
    $("#mt_hp").css("background-color", "#e9ecef");
    timer = setInterval(function () {
        min = parseInt(time / 60);
        sec = time % 60;
        $("#certi_hp").show();
        document.getElementById("m_hp_confirm_timer").innerHTML = "인증번호를 발송했습니다. (유효시간 : " + min + ":" + sec + ")";
        time--;
        if (time < -1) {
            clearInterval(timer);
            $("#certi_hp").hide();
            document.getElementById("m_hp_confirm_timer").innerHTML = "인증번호가 만료되었습니다. 재전송하여 다시 인증해 주세요.";
            $("#m_hp_chk_btn").prop("disabled", false);
            $("#m_hp_confirm_btn").prop("disabled", true);
            $("#m_hp_chk_btn").removeClass("btn-secondary");
            $("#m_hp_chk_btn").addClass("btn-outline-primary");
            $("#mt_hp").prop("readonly", false);
            jalert("인증번호 유효시간이 만료 되었습니다.");
        }
    }, 1000);
}

function f_hp_confirm() {
    if ($("#mt_hp_confirm").val() == "") {
        $("#mt_hp_confirm").focus();
        alert("인증번호를 등록해주세요.");
        return false;
    }

    $.post(
        "./join_update.php",
        {
            act: "confirm_mt_hp",
            mt_hp: $("#mt_hp").val(),
            mt_hp_confirm: $("#mt_hp_confirm").val(),
        },
        function (data) {
            if (data == "Y") {
                clearInterval(timer);
                $("#mt_hp_chk").val("Y");
                $("#certi_hp").hide();
                document.getElementById("m_hp_confirm_timer").innerHTML = "인증되었습니다!";
                $("#m_hp_confirm_btn").prop("disabled", true);
                $("#mt_hp_confirm").prop("readonly", true);
                $("#mt_hp").prop("readonly", true);
                jalert("인증이 확인되었습니다.");
            } else {
                document.getElementById("m_hp_confirm_timer").innerHTML = "인증번호를 잘못 입력하셨습니다. 다시 입력해주세요.";
                jalert("인증이 확인되지 않습니다. 인증문자를 확인바랍니다.");
            }
        }
    );

    return false;
}

function f_localStorage_reset() {
    localStorage.clear();
}

function f_localStorage_reset_go(url) {
    localStorage.clear();
    location.href = url;
}

function f_opt_qty_jaego(p, t) {
    var i = $("#ct_opt_qty" + p).val();
    var ct_opt_jaego = $("#ct_opt_jaego" + p).val();
    var ct_opt_jaego_temp = $("#ct_opt_jaego_temp" + p).val();
    var rtn;

    if (ct_opt_jaego > 0) {
        if (parseInt(ct_opt_jaego) >= parseInt(i)) {
            rtn = parseInt(ct_opt_jaego) - parseInt(i);
        } else {
            rtn = parseInt(ct_opt_jaego) + parseInt(i);
        }

        console.log(rtn, ct_opt_jaego, i);

        if (rtn > ct_opt_jaego) {
            rtn = ct_opt_jaego;
        }

        $("#ct_opt_jaego_t" + p).html(rtn);
        $("#ct_opt_jaego_temp" + p).val(rtn);
    }
}

function f_pt_qty_up(p) {
    var i = $("#ct_opt_qty" + p).val();
    var ct_opt_jaego = $("#ct_opt_jaego" + p).val();

    if (ct_opt_jaego > 0) {
        ++i;

        if (i > ct_opt_jaego) {
            i = ct_opt_jaego;
        }
        $("#ct_opt_qty" + p).val(i);

        setTimeout(() => {
            f_opt_qty_jaego(p, "U");
        }, 100);
    }
}

function f_pt_qty_down(p) {
    var i = $("#ct_opt_qty" + p).val();

    if (i > 1) {
        --i;
    } else if (i == 0) {
        i = 1;
    }

    $("#ct_opt_qty" + p).val(i);

    setTimeout(() => {
        f_opt_qty_jaego(p, "D");
    }, 100);
}

function f_push_cart(p) {
    var j = $("#ct_opt_jaego" + p).val();

    if (j > 0) {
        $("#btn_cart" + p).prop("disabled", true);
        var i = $("#ct_opt_qty" + p).val();
        var t = $("#pt_type").val();

        f_opt_qty_jaego(p, "U");

        var form_data = new FormData();

        form_data.append("act", "push_cart");
        form_data.append("pt_idx", p);
        form_data.append("pt_type", t);
        form_data.append("ct_qty", i);

        $.ajax({
            data: form_data,
            type: "POST",
            enctype: "multipart/form-data",
            url: "./index_update.php",
            cache: false,
            timeout: 5000,
            contentType: false,
            processData: false,
            success: function (data) {
                if (data) {
                    f_get_cart_list(t);
                    $("#btn_cart" + p).prop("disabled", false);
                }
            },
            error: function (err) {},
        });
    }
}

function f_get_cart_list(t, o = "") {
    var form_data = new FormData();

    form_data.append("act", "cart_list");
    form_data.append("pt_type", t);
    if (o != "") {
        form_data.append("ot_code", o);
    }

    $.ajax({
        data: form_data,
        type: "POST",
        enctype: "multipart/form-data",
        url: "./index_update.php",
        cache: false,
        timeout: 5000,
        contentType: false,
        processData: false,
        success: function (data) {
            if (data) {
                if (t == "1") {
                    $("#booth_cart_list").html(data);
                } else {
                    $("#logis_cart_list").html(data);
                }
            }
        },
        error: function (err) {},
    });
}

function f_del_cart(c) {
    var form_data = new FormData();

    form_data.append("act", "del_cart");
    form_data.append("ct_idx", c);

    $.ajax({
        data: form_data,
        type: "POST",
        enctype: "multipart/form-data",
        url: "./index_update.php",
        cache: false,
        timeout: 5000,
        contentType: false,
        processData: false,
        success: function (data) {
            if (data == "Y") {
                location.reload();
            } else {
                console.log(data);
            }
        },
        error: function (err) {},
    });
}
function f_order_search_date_range(nm, sd, ed, tab="") {
    if(tab){
        $(`#sel_search_sdate_${tab}`).val(sd);
        $(`#sel_search_edate_${tab}`).val(ed);
    }else{
        $("#sel_search_sdate").val(sd);
        $("#sel_search_edate").val(ed);
    }
    $(".c_pt_selling_date_range").removeClass("active");
    $("#f_order_search_date_range" + nm).addClass("active");

    return false;
}
function f_order_search_date_range_custom(nm, sd, ed, tab="") {
    if(tab){
        $(`#sel_search_sdate_${tab}`).val(sd);
        $(`#sel_search_edate_${tab}`).val(ed);
    }else{
        $("#sel_search_sdate").val(sd);
        $("#sel_search_edate").val(ed);
    }
    $(".month-btn").removeClass("on");
    $("#f_order_search_date_range" + nm).addClass("on");

    f_get_box_list();
    return false;
}

function f_point_use_range_custom(nm, sd, ed, tab="") {
    $("#obj_orderby").val(nm);
    $(".usebreakdown-btn").removeClass("on");
    $("#f_usebreakdown-btn" + nm).addClass("on");

    f_get_box_list();
    return false;
}

function f_like(t_name, idx, mt_idx, element){
    // 버튼이 클릭된 상태인지 확인할 변수
    if (element.disabled) {
        // 이미 클릭 중인 상태면 처리하지 않음
        return false;
    }

    // 버튼을 클릭한 상태로 설정
    element.disabled = true;
    if(!mt_idx){
        jalert_url('로그인이 필요한 기능입니다.','./login');
        element.classList.toggle("on");
        // 버튼을 다시 활성화
        element.disabled = false;
        return false;
    }
    const like_count = parseInt(element.getAttribute('data-like-count'));

    var form_data = new FormData();
    form_data.append("act", "like_change");
    form_data.append("t_name", t_name);
    form_data.append("t_idx", idx);
    form_data.append("mt_idx", mt_idx);
    form_data.append("like_count", like_count);

    $.ajax({
        data: form_data,
        type: "POST",
        enctype: "multipart/form-data",
        url: "./like_update.php",
        cache: false,
        timeout: 5000,
        contentType: false,
        processData: false,
        success: function (response) {
            var data = JSON.parse(response);
            // console.log(data);
            if (data.chk_btn === 'M') {
                element.classList.remove("on");
                jalert('본인 글은 좋아요 불가합니다.');
            } else {
            }
            element.setAttribute('data-like-count', data.chk_like);
            element.parentElement.querySelector('.like-count').textContent = data.chk_like.toLocaleString();

        },
        error: function (err) {
            console.error('Error:', err);
            jalert('좋아요 기능을 처리하는 중 오류가 발생했습니다.');
        },
        complete: function() {
            // AJAX 요청이 완료되면 버튼을 다시 활성화
            element.disabled = false;
        }
    });
}
function f_like_product(t_name, idx, mt_idx, element){
    // 버튼이 클릭된 상태인지 확인할 변수
    if (element.disabled) {
        // 이미 클릭 중인 상태면 처리하지 않음
        return false;
    }

    // 버튼을 클릭한 상태로 설정
    element.disabled = true;
    if(!mt_idx){
        jalert_url('로그인이 필요한 기능입니다.','./login');
        element.classList.remove("on");
        // 버튼을 다시 활성화
        element.disabled = false;
        return false;
    }
    const like_count = parseInt(element.getAttribute('data-like-count'));

    var form_data = new FormData();

    form_data.append("act", "like_change");
    form_data.append("t_name", t_name);
    form_data.append("t_idx", idx);
    form_data.append("mt_idx", mt_idx);
    form_data.append("like_count", like_count);

    $.ajax({
        data: form_data,
        type: "POST",
        enctype: "multipart/form-data",
        url: "./like_update.php",
        cache: false,
        timeout: 5000,
        contentType: false,
        processData: false,
        success: function (response) {
            var data = JSON.parse(response);
            // console.log(data);
            if (data.chk_btn === 'Y') {
                element.classList.add("on");
            } else if (data.chk_btn === 'M') {
                element.classList.remove("on");
                jalert('본인 글은 좋아요 불가합니다.');
            } else {
                element.classList.remove("on");
            }
            element.setAttribute('data-like-count', data.chk_like);
            element.parentElement.querySelector('.like-count').textContent = data.chk_like.toLocaleString();

        },
        error: function (err) {
            console.error('Error:', err);
            jalert('좋아요 기능을 처리하는 중 오류가 발생했습니다.');
        },
        complete: function() {
            // AJAX 요청이 완료되면 버튼을 다시 활성화
            element.disabled = false;
        }
    });
}
function f_like_community(t_name, idx, mt_idx, element){

    // 버튼이 클릭된 상태인지 확인할 변수
    if (element.disabled) {
        // 이미 클릭 중인 상태면 처리하지 않음
        return false;
    }

    // 버튼을 클릭한 상태로 설정
    element.disabled = true;
    if(!mt_idx){
        jalert_url('로그인이 필요한 기능입니다.','./login');
        // 버튼을 다시 활성화
        element.disabled = false;
        return false;
    }
    const like_count = parseInt(element.getAttribute('data-like-count'));

    var form_data = new FormData();

    form_data.append("act", "like_change");
    form_data.append("t_name", t_name);
    form_data.append("t_idx", idx);
    form_data.append("mt_idx", mt_idx);
    form_data.append("like_count", like_count);

    $.ajax({
        data: form_data,
        type: "POST",
        enctype: "multipart/form-data",
        url: "./like_update.php",
        cache: false,
        timeout: 5000,
        contentType: false,
        processData: false,
        success: function (response) {
            var data = JSON.parse(response);
            // console.log(data);
            if (data.chk_btn === 'M') {
                element.classList.remove("on");
                jalert('본인 글은 좋아요 불가합니다.');
            } else if(data.chk_btn === 'N'){
                element.classList.remove("on");
                var heartImg = $(element).find('.heart-img');
                heartImg.attr('src', './img/heart_b_off.svg');
            }else {
                element.classList.add("on");
                var heartImg = $(element).find('.heart-img');
                heartImg.attr('src', './img/heart_b_on.svg');
            }
            element.setAttribute('data-like-count', data.chk_like);
            element.parentElement.querySelector('.like-count').textContent = data.chk_like.toLocaleString();

        },
        error: function (err) {
            console.error('Error:', err);
            jalert('좋아요 기능을 처리하는 중 오류가 발생했습니다.');
        },
        complete: function() {
            // AJAX 요청이 완료되면 버튼을 다시 활성화
            element.disabled = false;
        }
    });
}
function f_like_community2(t_name, idx, mt_idx, element){

    // 버튼이 클릭된 상태인지 확인할 변수
    if (element.disabled) {
        // 이미 클릭 중인 상태면 처리하지 않음
        return false;
    }

    // 버튼을 클릭한 상태로 설정
    element.disabled = true;
    if(!mt_idx){
        jalert_url('로그인이 필요한 기능입니다.','./login');
        // 버튼을 다시 활성화
        element.disabled = false;
        return false;
    }
    const like_count = parseInt(element.getAttribute('data-like-count'));

    var form_data = new FormData();

    form_data.append("act", "like_change");
    form_data.append("t_name", t_name);
    form_data.append("t_idx", idx);
    form_data.append("mt_idx", mt_idx);
    form_data.append("like_count", like_count);

    $.ajax({
        data: form_data,
        type: "POST",
        enctype: "multipart/form-data",
        url: "./like_update.php",
        cache: false,
        timeout: 5000,
        contentType: false,
        processData: false,
        success: function (response) {
            var data = JSON.parse(response);
            // console.log(data);
            if (data.chk_btn === 'M') {
                element.classList.remove("on");
                jalert('본인 글은 좋아요 불가합니다.');
            } else if(data.chk_btn === 'N'){
                element.classList.remove("on");
                $(element).attr('src', './img/heart_b_off.svg');
            }else {
                element.classList.add("on");
                $(element).attr('src', './img/heart_b_on.svg');
            }
            element.setAttribute('data-like-count', data.chk_like);

        },
        error: function (err) {
            console.error('Error:', err);
            jalert('좋아요 기능을 처리하는 중 오류가 발생했습니다.');
        },
        complete: function() {
            // AJAX 요청이 완료되면 버튼을 다시 활성화
            element.disabled = false;
        }
    });
}
function f_follow(mt_idx, ft_mt_idx, element){
    if(!mt_idx){
        jalert_url('로그인이 필요한 기능입니다.','./login');
        return false;
    }
    if(mt_idx == ft_mt_idx){
        jalert('본인은 팔로우 할 수 없습니다.');
        return false;
    }
    var form_data = new FormData();

    form_data.append("act", "follow_change");
    form_data.append("mt_idx", mt_idx);
    form_data.append("ft_mt_idx", ft_mt_idx);

    $.ajax({
        data: form_data,
        type: "POST",
        enctype: "multipart/form-data",
        url: "./follow_update.php",
        cache: false,
        timeout: 5000,
        contentType: false,
        processData: false,
        success: function (response) {
            var data = JSON.parse(response);
            console.log(data);
            if (data.chk_btn === 'Y') {
                element.classList.remove("follow");
                element.classList.add("following");
                element.textContent = "팔로잉";
            } else {
                element.classList.remove("following");
                element.classList.add("follow");
                element.textContent = "팔로우";
            }

        },
        error: function (err) {
            console.error('Error:', err);
            alert('팔로우 기능을 처리하는 중 오류가 발생했습니다.');
        },
    });
}

function search_delete_btn(i, o = "") {
    if (o) {
        var o_t = o;
    } else {
        var o_t = "delete";
    }

    $.confirm({
        title: "",
        content: "정말 삭제하시겠습니까?<br>삭제된 자료는 복구되지 않습니다.",
        buttons: {
            confirm: {
                btnClass: 'btn btn-primary btn-block',
                text: "확인",
                action: function () {
                    $.post(
                        './search_update',
                        {
                            obj_act: o_t,
                            idx: i,
                        },
                        function (data) {
                            if (data == "Y") {
                                jalert_url("삭제되었습니다.", "reload");
                            }else{
                                jalert_url("잘못된 접근입니다.", "reload");
                            }
                        }
                    );
                },
            },
            cancel: {
                btnClass: "btn btn-light text-black btn-block",
                text: "취소",
                action: function () {
                    close();
                },
            },
        },
    });

    return false;
}
function search_delete_all_btn(i, o = "") {
    if (o) {
        var o_t = o;
    } else {
        var o_t = "delete_all";
    }
    if(!i){
        jalert_url('로그인이 필요한 기능입니다.','./login');
    }
    $.confirm({
        title: "",
        content: "정말 삭제하시겠습니까?<br>삭제된 자료는 복구되지 않습니다.",
        buttons: {
            confirm: {
                btnClass: 'btn btn-primary btn-block',
                text: "확인",
                action: function () {
                    $.post(
                        './search_update',
                        {
                            obj_act: o_t,
                            idx: i,
                        },
                        function (data) {
                            if (data == "Y") {
                                jalert_url("삭제되었습니다.", "reload");
                            }else{
                                jalert_url("잘못된 접근입니다.", "reload");
                            }
                        }
                    );
                },
            },
            cancel: {
                btnClass: "btn btn-light text-black btn-block",
                text: "취소",
                action: function () {
                    close();
                },
            },
        },
    });

    return false;
}

function copyToClipboard(val) {
    var t = document.createElement("textarea");
    document.body.appendChild(t);
    t.value = val;
    t.select();
    document.execCommand('copy');
    document.body.removeChild(t);
    jalert('주소를 복사하였습니다');
}

function returnPay(data){
    var rsp = JSON.parse(data);
    if (!rsp.error_msg) {
        if(!rsp.imp_success){
            jalert(rsp.error_msg);
            return false;
        }

        var form_data = new FormData();
        form_data.append("act", "set_order_result");
        form_data.append("imp_uid", rsp.imp_uid);
        form_data.append("merchant_uid", rsp.merchant_uid);

        $("#splinner_modal").modal("show");
        $.ajax({
            data: form_data,
            type: "POST",
            enctype: "multipart/form-data",
            url: "./order_payment_update.php",
            cache: false,
            timeout: 10000,
            contentType: false,
            processData: false,
            sync: false,
            dataType: 'json',
            success: function(data) {
                // console.log('set_order_result',data);
                if (data['result'] == false) {
                    jalert(data['msg']);
                } else {
                    location.replace(data['data']['url']);
                }

                $("#payment_btn").prop("disabled", false);
                $("#splinner_modal").modal("hide");

            },
            error: function(err) {
                console.log(err);
            },
        });

    } else {
        jalert(rsp.error_msg);
    }
}
function f_sns_login_lib(type,id,name,email){
    var url;

    if(type === 'kakao'){
        url = '../lib/kakao_login.php';
    }else if(type === 'naver'){
        url = '../lib/naver_login.php';
    }else if(type === 'google'){
        url = '../lib/google_login.php';
    }else if(type === 'apple'){
        url = '../lib/apple_login.php';
    }
    // 데이터를 서버로 전송
    $.post(
        url,
        {
            act: 'app',
            id: id,
            name: name,
            email: email,
        },
        function(response) {
            // 서버 응답 처리
            var data = JSON.parse(response);
            location.replace(data.url);
        }
    ).fail(function(jqXHR, textStatus, errorThrown) {
        // 요청 실패 시 처리
        console.error('요청 실패:', errorThrown);
    });
}

var uploadFiles = [];
var uploadFiles2 = [];
function f_callback_image(files, obj_id, obj_name){
    if(obj_name == "pt_image" || obj_name == "mt_image" || obj_name == "ct_image"){
        try {
            var filesObj = JSON.parse(files);
            var obj_t = obj_name + obj_id;

            var dataurl = 'data:image/png;base64,' + filesObj.file;
            var arr = dataurl.split(','),
                mime = arr[0].match(/:(.*?);/)[1],
                bstr = atob(arr[arr.length - 1]),
                n = bstr.length,
                u8arr = new Uint8Array(n);
            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }

            if (!filesObj.type.match("image.*")) {
                jalert("확장자는 이미지 확장자만 가능합니다.");
                return;
            }
            if (filesObj.size > 120000000) {
                jalert("업로드는 100메가 이하만 가능합니다.");
                return;
            }

            // 파일을 FormData에 추가할 때 obj_t 값을 함께 추가
            var fileWithMeta = {
                file: new File([u8arr], filesObj.name, { name: filesObj.name, type: filesObj.type, data: dataurl }),
                obj_t: obj_t
            };

            uploadFiles.push(fileWithMeta);

            $("#" + obj_t + "_box").html('<div class="rect"><img src="' + dataurl + '" /></div>');
            $("#" + obj_t + "_del").show();
        } catch (error) {
            console.error("Error processing file: ", error);
            jalert("파일 처리 중 오류가 발생했습니다.");
        }
    }
    else{
        try {
            var filesObj = JSON.parse(files);
            var obj_t = obj_name + obj_id;

            var dataurl = 'data:image/png;base64,' + filesObj.file;
            var arr = dataurl.split(','),
                mime = arr[0].match(/:(.*?);/)[1],
                bstr = atob(arr[arr.length - 1]),
                n = bstr.length,
                u8arr = new Uint8Array(n);
            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }

            if (!filesObj.type.match("image.*")) {
                jalert("확장자는 이미지 확장자만 가능합니다.");
                return;
            }
            if (filesObj.size > 120000000) {
                jalert("업로드는 100메가 이하만 가능합니다.");
                return;
            }

            // 파일을 FormData에 추가할 때 obj_t 값을 함께 추가
            var fileWithMeta = {
                file: new File([u8arr], filesObj.name, { name: filesObj.name, type: filesObj.type, data: dataurl }),
                obj_t: obj_t
            };

            uploadFiles2.push(fileWithMeta);

            $("#" + obj_t + "_box").html('<div class="rect"><img src="' + dataurl + '" /></div>');
            $("#" + obj_t + "_del").show();
        } catch (error) {
            console.error("Error processing file: ", error);
            jalert("파일 처리 중 오류가 발생했습니다.");
        }
    }
}

function getAllowedExtensions(pct_idx) {
    if (pct_idx === 5) {
        //return ['pdf'];
        return ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'hwp', 'hwpx', 'pdf', 'jpg', 'jpeg', 'png', 'mp4'];
    } else {
        return ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'hwp', 'hwpx', 'pdf', 'jpg', 'jpeg', 'png', 'mp4'];
    }
}

function getMaxFileSize(fileExtension) {
    if (fileExtension === 'mp4') {
        return 1073741824; // 1GB = 1,073,741,824 bytes
    } else {
        return 524288000; // 500MB = 524,288,000 bytes
    }
}
var fileCount = 0;
const fileObjects = {};
var uploadVideos = [];
function f_callback_file(files, obj_id, obj_name, pct_idx){
    if(obj_name == 'pt_file'){ // 자료등록/수정
        var MAX_FILES = 10;
        const allowedExtensions = getAllowedExtensions(pct_idx);
        var file = JSON.parse(files);

        const fileExtension = file.name.split('.').pop().toLowerCase(); // 파일 확장자 추출 및 소문자로 변환
        const maxFileSize = getMaxFileSize(fileExtension);

        // 확장자 검사
        if (!allowedExtensions.includes(fileExtension)) {
            jalert(`허용되지 않는 파일 형식입니다: ${file.name}`);
            return;
        }

        if (obj_id >= MAX_FILES) {
            jalert("최대 10개의 파일만 업로드할 수 있습니다.");
            return;
        }
        // 파일 크기 검사
        if (file.size > maxFileSize) {
            jalert(`업로드는 ${fileExtension === 'mp4' ? '1GB' : '500MB'} 이하만 가능합니다: ${file.name}`);
            return;
        }

        // 파일 정보 객체에 저장
        fileObjects['pt_file' + (fileCount + 1)] = file;
        // jalert('File added to fileObjects: ' + JSON.stringify(fileObjects));

        fileCount++;

        // UI 업데이트
        const obj_t = 'pt_file' + fileCount;
        $("#" + obj_t + "_box").show();
        $("#" + obj_t + "_box").html('<p>' + file.name + ' </p><button type="button" id="' + obj_t + '_btn" onclick="removeFile(' + fileCount + ', \'pt_file\');"><label id="' + obj_t + '_del"><img src="./img/g-delete.svg" alt=""></label></button>');
        $("#" + obj_t + "_rep_box").show();
        $("#" + obj_t + "_rep_text").html(file.name);
        $("#" + obj_t + "_del").show();
    }
    else{ // 커뮤니티 파일 업로드
        try {
            var filesObj = JSON.parse(files);
            var obj_t = obj_name + obj_id;

            var dataurl = 'data:image/png;base64,' + filesObj.file;
            var arr = dataurl.split(','),
                mime = arr[0].match(/:(.*?);/)[1],
                bstr = atob(arr[arr.length - 1]),
                n = bstr.length,
                u8arr = new Uint8Array(n);
            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }

            if (!filesObj.type.match("video.*")) {
                jalert("확장자는 동영상 확장자만 가능합니다.");
                return;
            }
            if (filesObj.size > 573741824) { // 1GB = 1,073,741,824 bytes
                jalert("업로드는 500MB 이하만 가능합니다.");
                return;
            }

            // 파일을 FormData에 추가할 때 obj_t 값을 함께 추가
            var fileWithMeta = {
                file: new File([u8arr], filesObj.name, { name: filesObj.name, type: filesObj.type, data: dataurl }),
                obj_t: obj_t
            };

            uploadVideos.push(fileWithMeta);

            $("#" + obj_t + "_box").show();
            $("#" + obj_t + "_box").html('<p>' + filesObj.name + ' </p><button onclick="f_preview_file_delete(\'' + obj_id + "', '" + obj_name + '\');"><label id="'+obj_name+ obj_id+'_del"><img src="./img/g-delete.svg" alt=""></label></button>');
            $("#" + obj_t + "_rep_box").show();
            $("#" + obj_t + "_rep_text").html(filesObj.name);
            $("#" + obj_t + "_del").show();
            $("#" + obj_t + "_rep_box").show();
        } catch (error) {
            console.error("Error processing file: ", error);
            jalert("파일 처리 중 오류가 발생했습니다.");
        }
    }
}

function f_profile_link(mt_idx){
    if(mt_idx) {
        location.href='./membership_profile?mt_idx=' + mt_idx;
    }else{
        jalert('해당 회원 정보가 없습니다.');
    }
}