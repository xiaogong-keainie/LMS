<?php
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

try {
    $pdo = getConnection();

    // 查询分类树结构（使用自连接查询）
    $stmt = $pdo->prepare("
        SELECT
            c1.category_id,
            c1.category_name,
            c1.parent_id AS parent_category_id,
            c2.category_name AS parent_category_name
        FROM Category c1
        LEFT JOIN Category c2 ON c1.parent_id = c2.category_id
    ");
    $stmt->execute();

    $results = $stmt->fetchAll();

    sendResponse(0, '查询成功', $results);

} catch (Exception $e) {
    sendResponse(1, '查询失败: ' . $e->getMessage(), null);
}
?>