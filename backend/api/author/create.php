<?php
// 新增作者接口
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // 验证输入参数
    if (!validateInput($input, ['name'])) {
        sendResponse(1, '缺少必要参数: name');
    }

    $name = trim($input['name']);
    $country = isset($input['country']) ? trim($input['country']) : '';

    try {
        $pdo = getConnection();

        // 检查作者是否已存在（基于姓名）
        $stmt = $pdo->prepare("SELECT author_id FROM Author WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            sendResponse(1, '作者已存在');
        }

        // 开始事务
        $pdo->beginTransaction();

        // 插入作者信息
        $stmt = $pdo->prepare("INSERT INTO Author (name, country) VALUES (?, ?)");
        $result = $stmt->execute([$name, $country]);

        if ($result) {
            $authorId = $pdo->lastInsertId();

            // 提交事务
            $pdo->commit();

            sendResponse(0, '作者创建成功', [
                'author_id' => $authorId
            ]);
        } else {
            // 回滚事务
            $pdo->rollback();
            sendResponse(1, '作者创建失败');
        }

    } catch (Exception $e) {
        // 回滚事务
        $pdo->rollback();
        sendResponse(1, '作者创建失败: ' . $e->getMessage());
    }
} else {
    sendResponse(1, '请求方法不正确');
}
?>