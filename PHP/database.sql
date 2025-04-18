-- 문자셋 설정
SET NAMES utf8mb4;
SET character_set_client = utf8mb4;
SET character_set_connection = utf8mb4;
SET character_set_results = utf8mb4;

-- 외래 키 제약조건 비활성화
SET FOREIGN_KEY_CHECKS = 0;

-- 기존 테이블 모두 삭제
DROP TABLE IF EXISTS chat_messages;
DROP TABLE IF EXISTS chat_variable_values;
DROP TABLE IF EXISTS chat_sessions;
DROP TABLE IF EXISTS chatbot_usage;
DROP TABLE IF EXISTS chatbot_variable_t;
DROP TABLE IF EXISTS point_t;
DROP TABLE IF EXISTS point_history_t;
DROP TABLE IF EXISTS chatbot_prompt_t;
DROP TABLE IF EXISTS chatbot_history_t;
DROP TABLE IF EXISTS category_t;
DROP TABLE IF EXISTS member_t;

-- 외래 키 제약조건 다시 활성화
SET FOREIGN_KEY_CHECKS = 1;

-- 데이터베이스 문자셋 설정
ALTER DATABASE chatbot_test CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 데이터베이스 생성
CREATE DATABASE IF NOT EXISTS chatbot_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE chatbot_test;

-- 회원 테이블
CREATE TABLE IF NOT EXISTS member_t (
    mt_idx int AUTO_INCREMENT PRIMARY KEY,
    mt_id varchar(255),
    mt_pwd varchar(255),
    mt_type tinyint(1),
    mt_level tinyint(1),
    mt_grade tinyint(1),
    mt_signuppath tinyint(1),
    mt_token_id text,
    mt_name varchar(50),
    mt_nickname varchar(50),
    mt_email varchar(255),
    mt_hp varchar(50),
    mt_birth date,
    mt_wdate datetime,
    mt_ldate datetime,
    mt_lgdate datetime,
    mt_rdate datetime,
    mt_adate datetime,
    mt_asdate datetime,
    mt_approval enum('Y','N','D'),
    mt_retire enum('Y','N'),
    mt_retire_memo varchar(255),
    mt_status enum('Y','N'),
    mt_agree1 enum('Y','N'),
    mt_agree2 enum('Y','N'),
    mt_show enum('Y','N'),
    mt_profile_show enum('Y','N'),
    mt_alarm_check enum('Y','N'),
    mt_admin_memo text,
    mt_image1 text,
    mt_image2 text,
    mt_point int,
    mt_level_point int,
    mt_bank int,
    mt_account_holder varchar(100),
    mt_account_number varchar(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 카테고리 테이블
CREATE TABLE IF NOT EXISTS category_t (
    ct_idx int AUTO_INCREMENT PRIMARY KEY,
    parent_idx int,
    ct_name varchar(100),
    ct_order int,
    ct_status enum('Y','N') DEFAULT 'Y'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 포인트 테이블
CREATE TABLE IF NOT EXISTS point_t (
    idx int AUTO_INCREMENT PRIMARY KEY,
    pt_show enum('Y','N'),
    mt_idx int,
    pt_idx int,
    ot_code varchar(50),
    ot_pcode varchar(50),
    pt_type enum('P','M','O'),
    pt_flag enum('01','02','03','04','05','09'),
    pt_price bigint,
    pt_use_point bigint,
    pt_expired int,
    pt_expire_date date,
    pt_memo text,
    pt_reason varchar(255),
    pt_wdate datetime,
    pt_mt_point bigint,
    FOREIGN KEY (mt_idx) REFERENCES member_t(mt_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 챗봇 변수 테이블
CREATE TABLE IF NOT EXISTS chatbot_variable_t (
    cv_idx int AUTO_INCREMENT PRIMARY KEY,
    ct_idx int,
    mt_idx int,
    cv_name varchar(100),
    cv_description text,
    cv_type enum('text','select','date','file') DEFAULT 'text',
    cv_options text,
    cv_required enum('Y','N') DEFAULT 'Y',
    cv_order int,
    cv_status enum('Y','N') DEFAULT 'Y',
    cv_wdate datetime,
    FOREIGN KEY (ct_idx) REFERENCES category_t(ct_idx),
    FOREIGN KEY (mt_idx) REFERENCES member_t(mt_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 챗봇 사용 기록 테이블
CREATE TABLE IF NOT EXISTS chatbot_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mt_idx INT NOT NULL,
    first_use_date DATETIME NOT NULL,
    FOREIGN KEY (mt_idx) REFERENCES member_t(mt_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 챗봇 세션 테이블
CREATE TABLE IF NOT EXISTS chat_sessions (
    cs_idx int AUTO_INCREMENT PRIMARY KEY,
    session_id varchar(100) NOT NULL,
    mt_idx int NOT NULL,
    ct_idx int NOT NULL,
    created_at datetime NOT NULL,
    status enum('active','completed','error') DEFAULT 'active',
    FOREIGN KEY (mt_idx) REFERENCES member_t(mt_idx),
    FOREIGN KEY (ct_idx) REFERENCES category_t(ct_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 채팅 변수 값 테이블
CREATE TABLE IF NOT EXISTS chat_variable_values (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cs_idx INT NOT NULL,
    cv_idx INT NOT NULL,
    value TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cs_idx) REFERENCES chat_sessions(cs_idx),
    FOREIGN KEY (cv_idx) REFERENCES chatbot_variable_t(cv_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 채팅 메시지 테이블
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cs_idx INT NOT NULL,
    content TEXT NOT NULL,
    is_bot BOOLEAN NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cs_idx) REFERENCES chat_sessions(cs_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 챗봇 프롬프트 테이블
CREATE TABLE IF NOT EXISTS chatbot_prompt_t (
    cp_idx int AUTO_INCREMENT PRIMARY KEY,
    parent_ct_idx int NOT NULL,
    ct_idx int NOT NULL,
    cp_title varchar(255) NOT NULL,
    cp_content text NOT NULL,
    cp_status enum('Y','N') DEFAULT 'Y',
    cp_wdate datetime NOT NULL,
    FOREIGN KEY (parent_ct_idx) REFERENCES category_t(ct_idx),
    FOREIGN KEY (ct_idx) REFERENCES category_t(ct_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 포인트 사용 내역 테이블
CREATE TABLE IF NOT EXISTS point_history_t (
    ph_idx INT AUTO_INCREMENT PRIMARY KEY,
    mt_idx INT NOT NULL,
    point_amount INT NOT NULL,
    point_type VARCHAR(50) NOT NULL,
    point_description TEXT,
    created_at DATETIME NOT NULL,
    main_ct_idx INT,
    ct_idx INT,
    FOREIGN KEY (mt_idx) REFERENCES member_t(mt_idx),
    FOREIGN KEY (main_ct_idx) REFERENCES category_t(ct_idx),
    FOREIGN KEY (ct_idx) REFERENCES category_t(ct_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 챗봇 히스토리 테이블
CREATE TABLE IF NOT EXISTS `chatbot_history_t` (
    `ch_idx` int(11) NOT NULL AUTO_INCREMENT,
    `mt_idx` int(11) NOT NULL COMMENT '사용자 idx',
    `main_ct_idx` int(11) NOT NULL COMMENT '상위 카테고리 idx',
    `ct_idx` int(11) NOT NULL COMMENT '하위 카테고리 idx',
    `ch_input` text NOT NULL COMMENT '사용자 입력',
    `ch_output` text NOT NULL COMMENT 'AI 응답',
    `ch_wdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
    PRIMARY KEY (`ch_idx`),
    KEY `mt_idx` (`mt_idx`),
    KEY `ct_idx` (`ct_idx`),
    KEY `main_ct_idx` (`main_ct_idx`),
    CONSTRAINT `chatbot_history_t_ibfk_1` FOREIGN KEY (`mt_idx`) REFERENCES `member_t` (`mt_idx`),
    CONSTRAINT `chatbot_history_t_ibfk_2` FOREIGN KEY (`ct_idx`) REFERENCES `category_t` (`ct_idx`),
    CONSTRAINT `chatbot_history_t_ibfk_3` FOREIGN KEY (`main_ct_idx`) REFERENCES `category_t` (`ct_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='챗봇 히스토리'; 

-- 테스트용 회원 데이터 추가
INSERT INTO member_t (
    mt_idx, mt_id, mt_pwd, mt_type, mt_level, mt_nickname, 
    mt_status, mt_approval, mt_show, mt_point, mt_wdate
) VALUES 
(1, 'test_user', 'password', 1, 1, '테스트사용자', 
    'Y', 'Y', 'Y', 1000, NOW()),
(2, 'admin', 'admin_pwd', 1, 9, '관리자', 
    'Y', 'Y', 'Y', 1000, NOW());

-- 테스트용 포인트 데이터 추가
INSERT INTO point_t (
    mt_idx, pt_show, pt_type, pt_flag, 
    pt_price, pt_use_point, pt_memo, pt_wdate, pt_mt_point
) VALUES 
(1, 'Y', 'P', '01', 1000, 0, '초기 포인트 지급', NOW(), 1000);

-- 테스트용 카테고리 데이터 추가
INSERT INTO category_t (ct_idx, parent_idx, ct_name, ct_order) VALUES
(1, NULL, '생활기록부', 1),
(2, NULL, '가정통신문', 2),
(3, NULL, '문제 제작', 3),
(4, 1, '행발', 1),
(5, 1, '교과세특', 2),
(6, 1, '창체', 3),
(7, 2, '가정통신문', 1),
(8, 3, '지필평가', 1),
(9, 3, '형성평가', 2);

-- 테스트용 변수 데이터 추가
INSERT INTO chatbot_variable_t (
    ct_idx, mt_idx, cv_name, cv_description, cv_type, cv_options, cv_required, cv_order, cv_wdate
) VALUES
-- 행발 변수
(4, 1, '핵심역량', '핵심역량을 선택해주세요', 'select', 
    '["자기관리 역량","지식정보처리 역량","창의적 사고 역량","심미적 감성 역량","의사소통 역량","공동체 역량"]', 
    'Y', 1, NOW()),
(4, 1, '인원수', '활동에 참여한 인원수를 입력해주세요', 'text', 
    NULL, 'Y', 2, NOW()),
(4, 1, '글자수', '작성할 글자수를 선택해주세요', 'select',
    '["600바이트","700바이트","1000바이트","1500바이트"]',
    'Y', 3, NOW()),
(4, 1, '기타 요구사항', '추가적인 요구사항을 입력해주세요', 'text',
    NULL, 'N', 4, NOW()),

-- 교과세특 변수
(5, 1, '교과', '해당 교과를 선택해주세요', 'select',
    '["국어","영어","수학","사회","역사","과학"]',
    'Y', 1, NOW()),
(5, 1, '활동 내용', '주요 활동 내용을 입력해주세요', 'text',
    NULL, 'Y', 2, NOW()),
(5, 1, '인원수', '활동에 참여한 인원수를 입력해주세요', 'text',
    NULL, 'Y', 3, NOW()),
(5, 1, '글자수', '작성할 글자수를 선택해주세요', 'select',
    '["600바이트","700바이트","1000바이트","1500바이트"]',
    'Y', 4, NOW()),

-- 창체 변수
(6, 1, '종류', '창의적 체험활동 종류를 선택해주세요', 'select',
    '["동아리 활동","자율활동","진로활동"]',
    'Y', 1, NOW()),
(6, 1, '활동 내용', '주요 활동 내용을 입력해주세요', 'text',
    NULL, 'Y', 2, NOW()),
(6, 1, '활동 날짜', '활동 날짜를 선택해주세요', 'date',
    NULL, 'Y', 3, NOW()),
(6, 1, '인원수', '활동에 참여한 인원수를 입력해주세요', 'text',
    NULL, 'Y', 4, NOW()),
(6, 1, '글자수', '작성할 글자수를 선택해주세요', 'select',
    '["600바이트","700바이트","1000바이트","1500바이트"]',
    'Y', 5, NOW()),

-- 가정통신문 변수
(7, 1, '종류', '가정통신문 종류를 선택해주세요', 'select',
    '["신학기 편지","성적통지표(전체)","성적통지표(개인)","학부모 문자"]',
    'Y', 1, NOW()),
(7, 1, '글자수', '작성할 글자수를 선택해주세요', 'select',
    '["600바이트","700바이트","1000바이트","1500바이트"]',
    'Y', 2, NOW()),
(7, 1, '꼭 전달해야 하는 내용', '반드시 포함되어야 하는 내용을 입력해주세요', 'text',
    NULL, 'Y', 3, NOW()),

-- 지필평가 변수
(8, 1, '학교급', '학교급을 선택해주세요', 'select',
    '["중학교","고등학교"]',
    'Y', 1, NOW()),
(8, 1, '출제과목', '출제 과목을 선택해주세요', 'select',
    '["국어","영어","수학","사회","과학","역사"]',
    'Y', 2, NOW()),
(8, 1, '출제 종류', '출제 종류를 선택해주세요', 'select',
    '["객관식","서술형"]',
    'Y', 3, NOW()),
(8, 1, '문제 수', '출제할 문제 수를 입력해주세요', 'text',
    NULL, 'Y', 4, NOW()),
(8, 1, '난이도', '문제의 난이도를 선택해주세요', 'select',
    '["상","중","하"]',
    'Y', 5, NOW()),
(8, 1, '문제종류', '문제의 종류를 선택해주세요', 'select',
    '["단순 암기 문제","추론 문제"]',
    'Y', 6, NOW()),
(8, 1, '참고 자료', 'PDF 파일을 업로드해주세요', 'file',
    NULL, 'N', 7, NOW()),

-- 형성평가 변수
(9, 1, '학교급', '학교급을 선택해주세요', 'select',
    '["중학교","고등학교"]',
    'Y', 1, NOW()),
(9, 1, '출제과목', '출제 과목을 선택해주세요', 'select',
    '["국어","영어","수학","사회","과학","역사"]',
    'Y', 2, NOW()),
(9, 1, '출제 종류', '출제 종류를 선택해주세요', 'select',
    '["객관식","O/X 문제"]',
    'Y', 3, NOW()),
(9, 1, '문제 수', '출제할 문제 수를 입력해주세요', 'text',
    NULL, 'Y', 4, NOW()),
(9, 1, '난이도', '문제의 난이도를 선택해주세요', 'select',
    '["상","중","하"]',
    'Y', 5, NOW()),
(9, 1, '문제종류', '문제의 종류를 선택해주세요', 'select',
    '["단순 암기 문제","추론 문제"]',
    'Y', 6, NOW()),
(9, 1, '참고 자료', 'PDF 파일을 업로드해주세요', 'file',
    NULL, 'N', 7, NOW());

-- 테스트용 프롬프트 데이터 추가
INSERT INTO chatbot_prompt_t (parent_ct_idx, ct_idx, cp_title, cp_content, cp_wdate) VALUES
-- 생활기록부 - 행발
(1, 4, '행발 작성 프롬프트', '다음 정보를 바탕으로 학생생활기록부 행동발달 특기사항을 작성해주세요:\n\n핵심역량: {핵심역량}\n참여 인원: {인원수}\n글자수 제한: {글자수}\n추가 요구사항: {기타 요구사항}', NOW()),

-- 생활기록부 - 교과세특
(1, 5, '교과세특 작성 프롬프트', '다음 정보를 바탕으로 학생생활기록부 교과세부특기사항을 작성해주세요:\n\n교과: {교과}\n활동 내용: {활동 내용}\n참여 인원: {인원수}\n글자수 제한: {글자수}', NOW()),

-- 생활기록부 - 창체
(1, 6, '창체 작성 프롬프트', '다음 정보를 바탕으로 학생생활기록부 창의적 체험활동 특기사항을 작성해주세요:\n\n활동 종류: {종류}\n활동 내용: {활동 내용}\n활동 날짜: {활동 날짜}\n참여 인원: {인원수}\n글자수 제한: {글자수}', NOW()),

-- 가정통신문
(2, 7, '가정통신문 작성 프롬프트', '다음 정보를 바탕으로 가정통신문을 작성해주세요:\n\n문서 종류: {종류}\n글자수 제한: {글자수}\n필수 포함 내용: {꼭 전달해야 하는 내용}', NOW()),

-- 문제 제작 - 지필평가
(3, 8, '지필평가 문제 생성 프롬프트', '다음 조건에 맞는 시험 문제를 생성해주세요:\n\n학교급: {학교급}\n과목: {출제과목}\n문제 유형: {출제 종류}\n문제 수: {문제 수}개\n난이도: {난이도}\n문제 유형: {문제종류}\n\n참고자료가 있다면 이를 반영하여 문제를 생성해주세요.', NOW()),

-- 문제 제작 - 형성평가
(3, 9, '형성평가 문제 생성 프롬프트', '다음 조건에 맞는 형성평가 문제를 생성해주세요:\n\n학교급: {학교급}\n과목: {출제과목}\n문제 유형: {출제 종류}\n문제 수: {문제 수}개\n난이도: {난이도}\n문제 유형: {문제종류}\n\n참고자료가 있다면 이를 반영하여 문제를 생성해주세요.', NOW()); 