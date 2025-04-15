<?
$_SUB_HEAD_TITLE = "문제제작"; //헤더에 타이틀명이 없을경우 공백
$_GET['hd_pc'] = '1';//PC hd 메뉴있음1, 메뉴없음 공백
$_GET['hd_num'] = '1';//모바일 hd 1~n까지 있음
$_GET['bt_menu'] = '1'; //모바일 하단메뉴 있음1, 없음 공백
include_once("./inc/head.php");
?>

<div class="wrap">
    <div class="sub_pg">
        <div class="container">
            <!-- 상단 서브배너 -->
            <div class="sub-top-banner relative">
                <div class="swiper">
                    <ul class="swiper-wrapper">
                        <li class="swiper-slide">
                            <a href="">
                                <img src="./img/ai-banner.jpg">
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- AI 리스트 -->
            <div id="ai-create-container">
                <h3 class="fs_40 fw_700">문제 제작</h3>
                <div id="ai-category">
                    <a class="fw_600" href="./question_written_test">지필평가</a>
                    <span class="fw_600 selected">형성평가</span>
                </div>
                <div class="ai-variable dropdown">
                    <div class="title"><span>학교급</span></div>
                    <div id="grade" class="ai-input-field" type="button" data-toggle="dropdown" aria-expanded="false">
                        <span id="grade_placeholder" class="placeholder">객관식 (중학교, 고등학교)</span>
                        <span id="selected_grade"></span>
                        <div class="dropdown-icon"></div>
                    </div>
                    <div class="dropdown-menu">
                        <button id="grade_middle" class="dropdown-item" onclick="select_grade_option('middle');">중학교</button>
                        <button id="grade_high" class="dropdown-item" onclick="select_grade_option('high');">고등학교</button>
                    </div>
                    <input id="grade_value" type="hidden" />
                </div>
                <div class="ai-variable dropdown">
                    <div class="title"><span>출제 과목</span></div>
                    <div id="subject" class="ai-input-field" type="button" data-toggle="dropdown" aria-expanded="false">
                        <span id="subject_placeholder" class="placeholder">객관식 (국어, 영어, 수학, 사회, 역사, 과학 등)</span>
                        <span id="selected_subject"></span>
                        <div class="dropdown-icon"></div>
                    </div>
                    <div class="dropdown-menu">
                        <button id="subject_ko" class="dropdown-item" onclick="select_subject_option('ko');">국어</button>
                        <button id="subject_en" class="dropdown-item" onclick="select_subject_option('en');">영어</button>
                        <button id="subject_ma" class="dropdown-item" onclick="select_subject_option('ma');">수학</button>
                        <button id="subject_so" class="dropdown-item" onclick="select_subject_option('so');">사회</button>
                        <button id="subject_hi" class="dropdown-item" onclick="select_subject_option('hi');">역사</button>
                        <button id="subject_sc" class="dropdown-item" onclick="select_subject_option('sc');">과학</button>
                    </div>
                    <input id="subject_value" type="hidden" />
                </div>
                <div class="ai-variable dropdown">
                    <div class="title"><span>출제 종류</span></div>
                    <div id="exam_kind" class="ai-input-field" type="button" data-toggle="dropdown" aria-expanded="false">
                        <span id="exam_kind_placeholder" class="placeholder">객관식 (O/X형, 객관식)</span>
                        <span id="selected_exam_kind"></span>
                        <div class="dropdown-icon"></div>
                    </div>
                    <div class="dropdown-menu">
                        <button id="exam_kind_ox" class="dropdown-item" onclick="select_exam_kind_option('ox');">O/X형</button>
                        <button id="exam_kind_objective" class="dropdown-item" onclick="select_exam_kind_option('objective');">객관식</button>
                    </div>
                    <input id="exam_kind_value" type="hidden" />
                </div>
                <div class="ai-variable">
                    <div class="title"><span>문제 수</span></div>
                    <input
                        class="ai-input-field"
                        type="number"
                        placeholder="단답식"
                        min="1" 
                        step="1"
                        onkeydown="return event.keyCode !== 190"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')" />
                </div>
                <div class="ai-variable dropdown">
                    <div class="title"><span>난이도</span></div>
                    <div id="difficulty" class="ai-input-field" type="button" data-toggle="dropdown" aria-expanded="false">
                        <span id="difficulty_placeholder" class="placeholder">객관식 (쉬움, 보통, 어려움)</span>
                        <span id="selected_difficulty"></span>
                        <div class="dropdown-icon"></div>
                    </div>
                    <div class="dropdown-menu">
                        <button id="difficulty_hard" class="dropdown-item" onclick="select_difficulty_option('hard');">상</button>
                        <button id="difficulty_normal" class="dropdown-item" onclick="select_difficulty_option('normal');">중</button>
                        <button id="difficulty_easy" class="dropdown-item" onclick="select_difficulty_option('easy');">하</button>
                    </div>
                    <input id="difficulty_value" type="hidden" />
                </div>
                <div class="ai-variable dropdown">
                    <div class="title"><span>문제 종류</span></div>
                    <div id="question_kind" class="ai-input-field" type="button" data-toggle="dropdown" aria-expanded="false">
                        <span id="question_kind_placeholder" class="placeholder">객관식 (단순 암기 문제, 추론문제(참고자료))</span>
                        <span id="selected_question_kind"></span>
                        <div class="dropdown-icon"></div>
                    </div>
                    <div class="dropdown-menu">
                        <button id="question_kind_simple" class="dropdown-item" onclick="select_question_kind_option('simple');">단순 암기 문제</button>
                        <button id="question_kind_inference" class="dropdown-item" onclick="select_question_kind_option('inference');">추론문제(참고자료)</button>
                    </div>
                    <input id="question_kind_value" type="hidden" />
                </div>
                <div class="ai-variable">
                    <div class="title"><span>출제 범위</span></div>
                    <div class="ai-input-field file-input-wrapper">
                        <input type="file" id="pdf_file" accept=".pdf" />
                        <span class="file-placeholder">출제할 범위의 PDF 파일 첨부</span>
                    </div>
                </div>
                <button class="btn-create fw_500">생성하기</button>
            </div>
        </div>
    </div>
</div>

<script>
var swiper = new Swiper(".sub-top-banner .swiper", {
    slidesPerView: 1,
    loop: true,
    navigation: {
        nextEl: ".banner-next",
        prevEl: ".banner-prev",
    },
});



const gradeNames = {
    'middle': '중학교',
    'high': '고등학교'
};
let selectedGrade = null;
function select_grade_option(grade) {
    const $gradeBtn = $(`#grade_${grade}`);

    if ($gradeBtn.hasClass('selected')) {
        return false;
    }

    if (selectedGrade !== null) {
        $(`#grade_${selectedGrade}`).removeClass('selected');
    }
    $gradeBtn.addClass('selected');
    $('#grade_placeholder').hide();
    $('#selected_grade').text(gradeNames[grade]);
    $('#grade_value').val(grade);
    selectedGrade = grade;

    return false;
}

const subjectNames = {
    'ko': '국어',
    'en': '영어',
    'ma': '수학',
    'so': '사회',
    'hi': '역사',
    'sc': '과학'
};
let selectedSubject = null;
function select_subject_option(subject) {
    const $subjectBtn = $(`#subject_${subject}`);

    if ($subjectBtn.hasClass('selected')) {
        return false;
    }

    if (selectedSubject !== null) {
        $(`#subject_${selectedSubject}`).removeClass('selected');
    }
    $subjectBtn.addClass('selected');
    $('#subject_placeholder').hide();
    $('#selected_subject').text(subjectNames[subject]);
    $('#subject_value').val(subject);
    selectedSubject = subject;

    return false;
}

const examKindNames = {
    'ox': 'O/X형',
    'objective': '객관식'
};
let selectedExamKind = null;
function select_exam_kind_option(exam_kind) {
    const $exam_kindBtn = $(`#exam_kind_${exam_kind}`);

    if ($exam_kindBtn.hasClass('selected')) {
        return false;
    }

    if (selectedExamKind !== null) {
        $(`#exam_kind_${selectedExamKind}`).removeClass('selected');
    }
    $exam_kindBtn.addClass('selected');
    $('#exam_kind_placeholder').hide();
    $('#selected_exam_kind').text(examKindNames[exam_kind]);
    $('#exam_kind_value').val(exam_kind);
    selectedExamKind = exam_kind;

    return false;
}

const difficultyNames = {
    'hard': '상',
    'normal': '중',
    'easy': '하'
};
let selectedDifficulty = null;
function select_difficulty_option(difficulty) {
    const $difficultyBtn = $(`#difficulty_${difficulty}`);

    if ($difficultyBtn.hasClass('selected')) {
        return false;
    }

    if (selectedDifficulty !== null) {
        $(`#difficulty_${selectedDifficulty}`).removeClass('selected');
    }
    $difficultyBtn.addClass('selected');
    $('#difficulty_placeholder').hide();
    $('#selected_difficulty').text(difficultyNames[difficulty]);
    $('#difficulty_value').val(difficulty);
    selectedDifficulty = difficulty;

    return false;
}

const questionKindNames = {
    'simple': '단순 암기 문제',
    'inference': '추론문제(참고자료)'
};
let selectedQuestionKind = null;
function select_question_kind_option(question_kind) {
    const $question_kindBtn = $(`#question_kind_${question_kind}`);

    if ($question_kindBtn.hasClass('selected')) {
        return false;
    }

    if (selectedQuestionKind !== null) {
        $(`#question_kind_${selectedQuestionKind}`).removeClass('selected');
    }
    $question_kindBtn.addClass('selected');
    $('#question_kind_placeholder').hide();
    $('#selected_question_kind').text(questionKindNames[question_kind]);
    $('#question_kind_value').val(question_kind);
    selectedQuestionKind = question_kind;

    return false;
}

document.getElementById('pdf_file').addEventListener('change', function() {
    const placeholder = this.parentElement.querySelector('.placeholder');
    if (this.files.length > 0) {
        placeholder.textContent = this.files[0].name;
        placeholder.classList.add('has-file');
    } else {
        placeholder.textContent = '출제할 범위의 PDF 파일 첨부';
        placeholder.classList.remove('has-file');
    }
});
</script>

<? include_once("./inc/tail.php"); ?>