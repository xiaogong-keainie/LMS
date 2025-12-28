<?php
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

try {
    $pdo = getConnection();

    // 查询逾期借阅记录（使用日期函数）
    $stmt = $pdo->prepare("
        SELECT
            b.borrow_id,
            b.user_id,
            u.username,
            b.book_id,
            bo.title AS book_title,
            b.borrow_date,
            b.due_date,
            DATEDIFF(NOW(), b.due_date) AS days_overdue
        FROM Borrow b
        INNER JOIN User u ON b.user_id = u.user_id
        INNER JOIN Book bo ON b.book_id = bo.book_id
        WHERE b.status = 'borrowed' AND b.due_date < NOW()
        ORDER BY days_overdue DESC
    ");
    $stmt->execute();

    $results = $stmt->fetchAll();

    sendResponse(0, '查询成功', $results);

} catch (Exception $e) {
    sendResponse(1, '查询失败: ' . $e->getMessage(), null);
}
?>