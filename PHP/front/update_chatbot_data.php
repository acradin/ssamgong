<?php
// 기본 설정
error_reporting(E_ALL);
ini_set('display_errors', 0); // 화면에 에러 표시하지 않음
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// JSON 헤더 설정
header('Content-Type: application/json');

// 로그 파일에 기록
function writeLog($message, $data = null) {
    $log = date('Y-m-d H:i:s') . " - " . $message;
    if ($data !== null) {
        $log .= "\n" . print_r($data, true);
    }
    error_log($log . "\n", 3, __DIR__ . '/debug.log');
}

try {
    writeLog("Request started");

    // 데이터베이스 연결 전 설정 파일 확인
    if (!file_exists('../config/database.php')) {
        throw new Exception('Database configuration file not found');
    }

    // 입력 데이터 받기
    $rawData = file_get_contents('php://input');
    writeLog("Received data", $rawData);

    if (empty($rawData)) {
        throw new Exception('No input data received');
    }

    // JSON 디코딩
    $data = json_decode($rawData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON decode error: ' . json_last_error_msg());
    }

    writeLog("Decoded data", $data);

    // 데이터베이스 연결
    require_once '../config/database.php';
    $db = Database::getInstance()->getConnection();
    writeLog("Database connected");

    try {
        // 프롬프트 업데이트
        if (isset($data['prompt'])) {
            // 카테고리 ID 유효성 검증 추가
            $categoryCheck = $db->rawQuery(
                "SELECT ct_idx FROM category_t WHERE ct_idx = ?",
                [$data['prompt']['ct_idx']]
            );
            
            if (empty($categoryCheck)) {
                throw new Exception('유효하지 않은 카테고리 ID입니다.');
            }

            $existingPrompt = $db->rawQuery(
                "SELECT * FROM chatbot_prompt_t 
                WHERE parent_ct_idx = ? AND ct_idx = ?",
                [$data['prompt']['parent_ct_idx'], $data['prompt']['ct_idx']]
            );

            if (!empty($existingPrompt)) {
                $db->update(
                    'chatbot_prompt_t',
                    [
                        'cp_title' => $data['prompt']['cp_title'],
                        'cp_content' => $data['prompt']['cp_content'],
                        'cp_wdate' => date('Y-m-d H:i:s')
                    ],
                    'parent_ct_idx = ? AND ct_idx = ?',
                    [$data['prompt']['parent_ct_idx'], $data['prompt']['ct_idx']]
                );
            } else {
                $db->insert('chatbot_prompt_t', [
                    'parent_ct_idx' => $data['prompt']['parent_ct_idx'],
                    'ct_idx' => $data['prompt']['ct_idx'],
                    'cp_title' => $data['prompt']['cp_title'],
                    'cp_content' => $data['prompt']['cp_content'],
                    'cp_status' => 'Y',
                    'cp_wdate' => date('Y-m-d H:i:s')
                ]);
            }

            // 변수 업데이트
            if (isset($data['variables'])) {
                // 기존 변수 삭제 전 카테고리 확인
                $db->rawQuery(
                    "DELETE FROM chatbot_variable_t WHERE ct_idx = ?",
                    [$data['prompt']['ct_idx']]  // 올바른 카테고리 ID 사용
                );

                // 새 변수 추가
                foreach ($data['variables'] as $index => $variable) {
                    $db->insert('chatbot_variable_t', [
                        'ct_idx' => $data['prompt']['ct_idx'],  // 올바른 카테고리 ID 사용
                        'cv_name' => $variable['cv_name'],
                        'cv_type' => $variable['cv_type'],
                        'cv_description' => $variable['cv_description'],
                        'cv_order' => $index + 1,
                        'cv_status' => 'Y',
                        'cv_wdate' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        throw $e;
    }

} catch (Exception $e) {
    writeLog("Error occurred", [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>