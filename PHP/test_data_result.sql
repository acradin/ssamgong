-- 문자 인코딩 설정
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 기존 테스트 데이터 삭제
DELETE FROM chat_messages;
DELETE FROM chat_sessions;

-- 테스트용 카테고리 데이터 생성 (없는 경우)
INSERT IGNORE INTO category_t (ct_idx, parent_idx, ct_name, ct_order, ct_status) VALUES
(1, NULL, '수업자료', 1, 'Y'),
(2, NULL, '학급운영', 2, 'Y'),
(3, NULL, '업무관리', 3, 'Y'),
(4, 1, '교과별자료', 1, 'Y'),
(5, 1, '평가자료', 2, 'Y'),
(6, 2, '학급행사', 1, 'Y'),
(7, 2, '상담기록', 2, 'Y'),
(8, 3, '공문처리', 1, 'Y'),
(9, 3, '보고서작성', 2, 'Y');

-- 채팅 세션 생성
INSERT INTO chat_sessions (session_id, mt_idx, ct_idx, created_at)
WITH RECURSIVE numbers AS (
    SELECT 1 AS n
    UNION ALL
    SELECT n + 1 FROM numbers WHERE n < 200
)
SELECT 
    CONCAT('SESSION_', LPAD(n, 3, '0')),
    (SELECT mt_idx FROM member_t ORDER BY RAND() LIMIT 1),
    (SELECT ct_idx FROM category_t WHERE parent_idx IS NOT NULL ORDER BY RAND() LIMIT 1),
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 720) HOUR)
FROM numbers;

-- 채팅 메시지 생성
INSERT INTO chat_messages (cs_idx, content, is_bot, created_at)
WITH RECURSIVE message_numbers AS (
    SELECT 
        cs.cs_idx,
        1 AS msg_order,
        cs.created_at AS session_time
    FROM chat_sessions cs
    UNION ALL
    SELECT 
        cs_idx,
        msg_order + 1,
        session_time
    FROM message_numbers 
    WHERE msg_order < 10
)
SELECT 
    mn.cs_idx,
    CASE 
        WHEN mn.msg_order % 2 = 1 THEN
            CASE FLOOR(RAND() * 5)
                WHEN 0 THEN '안녕하세요, 수업 자료 준비하는데 도움이 필요합니다.'
                WHEN 1 THEN '학급 운영에 대해 조언을 구하고 싶습니다.'
                WHEN 2 THEN '공문 작성 방법을 알려주세요.'
                WHEN 3 THEN '학부모 상담 기록 작성은 어떻게 하나요?'
                ELSE '교과별 평가 기준을 설정하고 싶습니다.'
            END
        ELSE
            CASE FLOOR(RAND() * 5)
                WHEN 0 THEN '네, 어떤 과목의 수업 자료가 필요하신가요? 학년과 과목을 알려주시면 맞춤형 자료를 추천해드리겠습니다.'
                WHEN 1 THEN '학급 운영에서 특별히 고민되는 부분이 있으신가요? 학급 규칙 설정, 일과 운영, 특별활동 계획 등 구체적인 부분을 말씀해주시면 도움을 드리겠습니다.'
                WHEN 2 THEN '공문 작성 시 필요한 기본 양식과 주의사항을 안내해드리겠습니다. 어떤 종류의 공문을 작성하시나요?'
                WHEN 3 THEN '학부모 상담 기록은 다음과 같은 항목들을 포함하면 좋습니다: 1. 상담 일시 2. 상담 대상 3. 상담 내용 4. 후속 조치 계획'
                ELSE '교과별 평가 기준 설정을 위해 교육과정을 먼저 검토하시는 것이 좋습니다. 해당 과목의 성취기준을 바탕으로 평가 계획을 수립하시면 됩니다.'
            END
        END AS content,
    mn.msg_order % 2 = 0 AS is_bot,
    DATE_ADD(mn.session_time, INTERVAL (mn.msg_order * 2) MINUTE)
FROM message_numbers mn
ORDER BY mn.cs_idx, mn.msg_order;

-- 세션별 메시지 수 확인
SELECT cs.session_id, COUNT(cm.id) as message_count
FROM chat_sessions cs
LEFT JOIN chat_messages cm ON cs.cs_idx = cm.cs_idx
GROUP BY cs.session_id;