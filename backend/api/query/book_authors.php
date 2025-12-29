<?php
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

// 获取查询参数
$book_id = $_GET['book_id'] ?? null;

try {
    $pdo = getConnection();

    if ($book_id) {
        // 根据指定图书ID查询作者信息
        $stmt = $pdo->prepare("
            SELECT
                b.book_id,
                b.title AS book_title,
                a.author_id,
                a.name AS author_name,
                a.country
            FROM Book b
            INNER JOIN BookAuthor ba ON b.book_id = ba.book_id
            INNER JOIN Author a ON ba.author_id = a.author_id
            WHERE b.book_id = ?
        ");
        $stmt->execute([$book_id]);
    } else {
        // 查询所有图书及其作者信息
        $stmt = $pdo->prepare("
            SELECT
                b.book_id,
                b.title AS book_title,
                a.author_id,
                a.name AS author_name,
                a.country
            FROM Book b
            INNER JOIN BookAuthor ba ON b.book_id = ba.book_id
            INNER JOIN Author a ON ba.author_id = a.author_id
        ");
        $stmt->execute();
    }

    $results = $stmt->fetchAll();

    sendResponse(0, '查询成功', $results);

} catch (Exception $e) {
    sendResponse(1, '查询失败: ' . $e->getMessage(), null);
}
?>