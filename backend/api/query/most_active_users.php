<?php
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

try {
    $pdo = getConnection();

    // 查询最活跃的用户（借阅次数最多的用户，使用相关子查询逻辑）
    $stmt = $pdo->prepare("
        SELECT
            u.user_id,
            u.username,
            COUNT(b.borrow_id) AS borrow_count
        FROM User u
        INNER JOIN Borrow b ON u.user_id = b.user_id
        GROUP BY u.user_id, u.username
        HAVING COUNT(b.borrow_id) = (
            SELECT MAX(borrow_counts.cnt)
            FROM (
                SELECT COUNT(borrow_id) AS cnt
                FROM Borrow
                GROUP BY user_id
            ) AS borrow_counts
        )
        ORDER BY borrow_count DESC
    ");
    $stmt->execute();

    $results = $stmt->fetchAll();

    // 如果上面的查询没有结果，返回借阅次数最多的前几个用户
    if (empty($results)) {
        $stmt = $pdo->prepare("
            SELECT
                u.user_id,
                u.username,
                COUNT(b.borrow_id) AS borrow_count
            FROM User u
            INNER JOIN Borrow b ON u.user_id = b.user_id
            GROUP BY u.user_id, u.username
            ORDER BY borrow_count DESC
            LIMIT 5
        ");
        $stmt->execute();
        $results = $stmt->fetchAll();
    }

    sendResponse(0, '查询成功', $results);

} catch (Exception $e) {
    sendResponse(1, '查询失败: ' . $e->getMessage(), null);
}
?>