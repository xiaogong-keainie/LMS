<?php
// 路由器文件，用于处理所有请求
if (preg_match('/^\/api\/.*/', $_SERVER['REQUEST_URI'])) {
    // 将所有 /api/* 请求重定向到 api/index.php
    require_once 'api/index.php';
} else {
    // 对于其他请求，尝试直接访问文件，如果不存在则返回 404
    $file = $_SERVER['REQUEST_URI'];
    $path = parse_url($file, PHP_URL_PATH);
    $filepath = __DIR__ . $path;

    if (file_exists($filepath) && is_file($filepath)) {
        return false; // 让服务器处理静态文件
    } else {
        http_response_code(404);
        echo "Not Found";
    }
}
?>