<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib.inc.php";

if (!isset($_GET['ct_idx'])) {
    echo json_encode(['success' => false, 'message' => '잘못된 접근입니다.']);
    exit;
}

$categoryId = (int)$_GET['ct_idx'];

$variables = $DB->rawQuery("
    SELECT cv_idx, cv_name, cv_type, cv_description, cv_options, cv_required
    FROM chatbot_variable_t
    WHERE ct_idx = ? AND cv_status = 'Y'
    ORDER BY cv_order",
    [$categoryId]
);

echo json_encode(['success' => true, 'variables' => $variables]);