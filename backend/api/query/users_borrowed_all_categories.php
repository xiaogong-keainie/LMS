<?php
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

try {
    $pdo = getConnection();

    // 查询借阅过所有分类图书的用户（关系除法查询）
    // 这个查询找出借阅过所有图书分类的用户
    $stmt = $pdo->prepare("
        SELECT
            u.user_id,
            u.username
        FROM User u
        WHERE NOT EXISTS (
            SELECT c.category_id
            FROM Category c
            WHERE c.category_id IS NOT NULL
            AND NOT EXISTS (
                SELECT 1
                FROM Borrow b
                INNER JOIN Book bo ON b.book_id = bo.book_id
                WHERE b.user_id = u.user_id
                AND bo.category_id = c.category_id
            )
        )
        AND u.user_id IN (
            SELECT DISTINCT b.user_id
            FROM Borrow b
            INNER JOIN Book bo ON b.book_id = bo.book_id
            WHERE bo.category_id IS NOT NULL
        )
    ");
    $stmt->execute();

    $results = $stmt->fetchAll();

    sendResponse(0, '查询成功', $results);

} catch (Exception $e) {
    sendResponse(1, '查询失败: ' . $e->getMessage(), null);
}
?>