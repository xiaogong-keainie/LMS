<?php
// API入口文件 - 路由分发
require_once '../config.php';
require_once '../utils.php';

// 获取请求路径
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// 移除项目根路径部分，获取API路径
$api_path = str_replace('/final_proj/backend/api', '', $path);

// 根据API路径分发请求
if ($api_path === '/user/register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'user/register.php';
} elseif ($api_path === '/user/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'user/login.php';
} else {
    // 更多API路由...
    sendResponse(1, '接口不存在', null);
}
?>