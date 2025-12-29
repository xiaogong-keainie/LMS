<?php
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

try {
    $pdo = getConnection();

    // 按分类统计图书数量（GROUP BY查询）
    $stmt = $pdo->prepare("
        SELECT
            c.category_id,
            c.category_name,
            COUNT(b.book_id) AS book_count
        FROM Category c
        LEFT JOIN Book b ON c.category_id = b.category_id
        GROUP BY c.category_id, c.category_name
        ORDER BY book_count DESC
    ");
    $stmt->execute();

    $results = $stmt->fetchAll();

    sendResponse(0, '查询成功', $results);

} catch (Exception $e) {
    sendResponse(1, '查询失败: ' . $e->getMessage(), null);
}
?>