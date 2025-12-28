<?php
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

// 获取查询参数
$user_id = $_GET['user_id'] ?? null;

try {
    $pdo = getConnection();

    if ($user_id) {
        // 查询指定用户的借阅详情
        $stmt = $pdo->prepare("
            SELECT
                b.borrow_id,
                b.user_id,
                u.username,
                b.book_id,
                bo.title AS book_title,
                GROUP_CONCAT(a.name SEPARATOR ', ') AS author_names,
                c.category_name,
                b.borrow_date,
                b.due_date,
                b.return_date,
                b.status
            FROM Borrow b
            INNER JOIN User u ON b.user_id = u.user_id
            INNER JOIN Book bo ON b.book_id = bo.book_id
            INNER JOIN Category c ON bo.category_id = c.category_id
            LEFT JOIN BookAuthor ba ON bo.book_id = ba.book_id
            LEFT JOIN Author a ON ba.author_id = a.author_id
            WHERE b.user_id = ?
            GROUP BY b.borrow_id, b.user_id, u.username, b.book_id, bo.title,
                     c.category_name, b.borrow_date, b.due_date, b.return_date, b.status
            ORDER BY b.borrow_date DESC
        ");
        $stmt->execute([$user_id]);
    } else {
        // 查询所有借阅详情
        $stmt = $pdo->prepare("
            SELECT
                b.borrow_id,
                b.user_id,
                u.username,
                b.book_id,
                bo.title AS book_title,
                GROUP_CONCAT(a.name SEPARATOR ', ') AS author_names,
                c.category_name,
                b.borrow_date,
                b.due_date,
                b.return_date,
                b.status
            FROM Borrow b
            INNER JOIN User u ON b.user_id = u.user_id
            INNER JOIN Book bo ON b.book_id = bo.book_id
            INNER JOIN Category c ON bo.category_id = c.category_id
            LEFT JOIN BookAuthor ba ON bo.book_id = ba.book_id
            LEFT JOIN Author a ON ba.author_id = a.author_id
            GROUP BY b.borrow_id, b.user_id, u.username, b.book_id, bo.title,
                     c.category_name, b.borrow_date, b.due_date, b.return_date, b.status
            ORDER BY b.borrow_date DESC
        ");
        $stmt->execute();
    }

    $results = $stmt->fetchAll();

    sendResponse(0, '查询成功', $results);

} catch (Exception $e) {
    sendResponse(1, '查询失败: ' . $e->getMessage(), null);
}
?>