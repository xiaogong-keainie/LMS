<?php
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

// 获取路径参数
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
preg_match('/^\/api\/query\/users-borrowed-book\/(\d+)$/', $path, $matches);
if (!isset($matches[1])) {
    sendResponse(1, '图书ID参数错误', null);
}
$book_id = $matches[1];

try {
    $pdo = getConnection();

    // 查询借阅过指定图书的所有用户（使用子查询）
    $stmt = $pdo->prepare("
        SELECT
            u.user_id,
            u.username,
            b.borrow_date
        FROM User u
        INNER JOIN Borrow b ON u.user_id = b.user_id
        WHERE b.book_id = ?
        ORDER BY b.borrow_date DESC
    ");
    $stmt->execute([$book_id]);

    $results = $stmt->fetchAll();

    sendResponse(0, '查询成功', $results);

} catch (Exception $e) {
    sendResponse(1, '查询失败: ' . $e->getMessage(), null);
}
?>