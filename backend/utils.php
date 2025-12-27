<?php
// API响应格式化函数
function sendResponse($code, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'code' => $code,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// 验证输入参数
function validateInput($data, $required_fields) {
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return false;
        }
    }
    return true;
}
?>