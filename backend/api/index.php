<?php
// API入口文件 - 路由分发
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils.php';

// 获取请求路径
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// 移除项目根路径部分，获取API路径
$api_path = str_replace('/api', '', $path);

// 定义路由映射表
$routing_table = [
    // 用户模块
    ['path' => '/user/register', 'method' => 'POST', 'file' => 'user/register.php'],
    ['path' => '/user/login', 'method' => 'POST', 'file' => 'user/login.php'],

    // 图书模块
    ['path' => '/book/create', 'method' => 'POST', 'file' => 'book/create.php'],
    ['path' => '/book/list', 'method' => 'GET', 'file' => 'book/list.php'],

    // 作者模块
    ['path' => '/author/create', 'method' => 'POST', 'file' => 'author/create.php'],
    ['path' => '/author/list', 'method' => 'GET', 'file' => 'author/list.php'],

    // 分类模块
    ['path' => '/category/create', 'method' => 'POST', 'file' => 'category/create.php'],
    ['path' => '/category/list', 'method' => 'GET', 'file' => 'category/list.php'],

    // 借阅模块
    ['path' => '/borrow/create', 'method' => 'POST', 'file' => 'borrow/create.php'],

    // 查询统计模块
    ['path' => '/query/book-authors', 'method' => 'GET', 'file' => 'query/book_authors.php'],
    ['path' => '/query/category-tree', 'method' => 'GET', 'file' => 'query/category_tree.php'],
    ['path' => '/query/book-count-by-category', 'method' => 'GET', 'file' => 'query/book_count_by_category.php'],
    ['path' => '/query/overdue-borrow', 'method' => 'GET', 'file' => 'query/overdue_borrow.php'],
    ['path' => '/query/most-active-users', 'method' => 'GET', 'file' => 'query/most_active_users.php'],
    ['path' => '/query/active-or-admin-users', 'method' => 'GET', 'file' => 'query/active_or_admin_users.php'],
    ['path' => '/query/borrow-detail', 'method' => 'GET', 'file' => 'query/borrow_detail.php'],
    ['path' => '/query/users-borrowed-all-categories', 'method' => 'GET', 'file' => 'query/users_borrowed_all_categories.php'],
];

// 检查静态路由
foreach ($routing_table as $route) {
    if ($api_path === $route['path'] && $_SERVER['REQUEST_METHOD'] === $route['method']) {
        require_once $route['file'];
        exit;
    }
}

// 检查动态路由（包含正则表达式的路由）
$dynamic_routes = [
    ['pattern' => '/^\/book\/update\/(\d+)$/', 'method' => 'PUT', 'file' => 'book/update.php'],
    ['pattern' => '/^\/book\/delete\/(\d+)$/', 'method' => 'DELETE', 'file' => 'book/delete.php'],
    ['pattern' => '/^\/borrow\/return\/(\d+)$/', 'method' => 'PUT', 'file' => 'borrow/return.php'],
    ['pattern' => '/^\/borrow\/user\/(\d+)$/', 'method' => 'GET', 'file' => 'borrow/user_borrows.php'],
    ['pattern' => '/^\/query\/users-borrowed-book\/(\d+)$/', 'method' => 'GET', 'file' => 'query/users_borrowed_book.php'],
];

foreach ($dynamic_routes as $route) {
    if (preg_match($route['pattern'], $path) && $_SERVER['REQUEST_METHOD'] === $route['method']) {
        require_once $route['file'];
        exit;
    }
}

// 如果没有匹配的路由
sendResponse(1, '接口不存在', null);
?>