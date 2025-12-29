<?php
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

try {
    $pdo = getConnection();

    // 查询活跃用户或管理员（使用UNION实现集合运算）
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            u.user_id,
            u.username,
            r.role_name AS role
        FROM User u
        INNER JOIN UserRole ur ON u.user_id = ur.user_id
        INNER JOIN Role r ON ur.role_id = r.role_id
        WHERE r.role_name = 'ADMIN'
        UNION
        SELECT DISTINCT
            u.user_id,
            u.username,
            'READER' AS role
        FROM User u
        INNER JOIN Borrow b ON u.user_id = b.user_id
        WHERE b.status = 'borrowed'
        ORDER BY user_id
    ");
    $stmt->execute();

    $results = $stmt->fetchAll();

    sendResponse(0, '查询成功', $results);

} catch (Exception $e) {
    sendResponse(1, '查询失败: ' . $e->getMessage(), null);
}
?>