SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 기존 데이터 삭제
DELETE FROM point_history_t;

-- 먼저 실제 member_t에 있는 사용자의 mt_idx 확인
SET @user_id = (SELECT mt_idx FROM member_t WHERE mt_level = 1 LIMIT 1);
SET @admin_id = (SELECT mt_idx FROM member_t WHERE mt_level = 9 LIMIT 1);

-- 포인트 내역 테스트 데이터
INSERT INTO point_history_t (mt_idx, point_amount, point_type, point_description, created_at) 
VALUES 
(@user_id, 1000, 'signup_bonus', '회원가입 보너스', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(@user_id, -200, 'chatbot_use', '챗봇 사용 (생활기록부)', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(@user_id, -300, 'chatbot_use', '챗봇 사용 (수업지도안)', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(@admin_id, 5000, 'admin_bonus', '관리자 초기 포인트', DATE_SUB(NOW(), INTERVAL 7 DAY));

-- 랜덤 데이터 생성 (1000개)
INSERT INTO point_history_t (mt_idx, point_amount, point_type, point_description, created_at)
SELECT 
    CASE WHEN RAND() < 0.5 THEN @user_id ELSE @admin_id END as mt_idx,
    CASE 
        WHEN RAND() < 0.7 THEN -(FLOOR(100 + RAND() * 900))
        ELSE FLOOR(100 + RAND() * 1000)
    END as point_amount,
    ELT(1 + FLOOR(RAND() * 3), 'chatbot_use', 'daily_bonus', 'event_reward') as point_type,
    CASE ELT(1 + FLOOR(RAND() * 3), 'chatbot_use', 'daily_bonus', 'event_reward')
        WHEN 'chatbot_use' THEN CONCAT('챗봇 사용 (', 
            ELT(1 + FLOOR(RAND() * 4), 
                '생활기록부',
                '수업지도안',
                '업무자동화',
                '가정통신문'
            ), ')')
        WHEN 'daily_bonus' THEN '일일 출석 보너스'
        ELSE '이벤트 보상'
    END as point_description,
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 365) DAY) as created_at
FROM 
    (SELECT @row := 0) r,
    (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) t1,
    (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) t2
LIMIT 1000;