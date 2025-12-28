<?php
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

// 获取路径参数
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
preg_match('/^\/api\/borrow\/user\/(\d+)$/', $path, $matches);
if (!isset($matches[1])) {
    sendResponse(1, '用户ID参数错误', null);
}
$user_id = $matches[1];

try {
    $pdo = getConnection();

    // 查询用户的借阅记录
    $stmt = $pdo->prepare("
        SELECT
            b.borrow_id,
            b.user_id,
            u.username,
            b.book_id,
            bo.title AS book_title,
            b.borrow_date,
            b.due_date,
            b.return_date,
            b.status
        FROM Borrow b
        LEFT JOIN User u ON b.user_id = u.user_id
        LEFT JOIN Book bo ON b.book_id = bo.book_id
        WHERE b.user_id = ?
        ORDER BY b.borrow_date DESC
    ");
    $stmt->execute([$user_id]);
    $borrow_records = $stmt->fetchAll();

    sendResponse(0, '查询成功', $borrow_records);

} catch (Exception $e) {
    sendResponse(1, '查询失败: ' . $e->getMessage(), null);
}
?>