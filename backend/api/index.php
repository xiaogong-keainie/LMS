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
} elseif ($api_path === '/book/create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'book/create.php';
} elseif (preg_match('/^\/book\/update\/(\d+)$/', $path) && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    require_once 'book/update.php';
} elseif (preg_match('/^\/book\/delete\/(\d+)$/', $path) && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    require_once 'book/delete.php';
} elseif ($api_path === '/book/list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once 'book/list.php';
} elseif ($api_path === '/author/create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'author/create.php';
} elseif ($api_path === '/author/list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once 'author/list.php';
} elseif ($api_path === '/category/create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'category/create.php';
} elseif ($api_path === '/category/list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once 'category/list.php';
} else {
    // 更多API路由...
    sendResponse(1, '接口不存在', null);
}
?>