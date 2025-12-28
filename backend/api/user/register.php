<?php
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

// 处理用户注册请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // 验证输入参数
    if (!validateInput($input, ['username', 'password', 'email'])) {
        sendResponse(1, '缺少必要参数');
    }

    $username = trim($input['username']);
    $password = trim($input['password']);
    $email = trim($input['email']);

    // 验证邮箱格式
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(1, '邮箱格式不正确');
    }

    try {
        $pdo = getConnection();

        // 检查用户名是否已存在
        $stmt = $pdo->prepare("SELECT user_id FROM User WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            sendResponse(1, '用户名已存在');
        }

        // 检查邮箱是否已存在
        $stmt = $pdo->prepare("SELECT user_id FROM User WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            sendResponse(1, '邮箱已被注册');
        }

        // 密码加密
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 开始事务
        $pdo->beginTransaction();

        // 插入用户信息
        $stmt = $pdo->prepare("INSERT INTO User (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, $email]);
        $userId = $pdo->lastInsertId();

        // 为新用户创建用户资料
        $stmt = $pdo->prepare("INSERT INTO UserProfile (user_id) VALUES (?)");
        $stmt->execute([$userId]);

        // 为新用户分配READER角色
        $stmt = $pdo->prepare("SELECT role_id FROM Role WHERE role_name = 'READER'");
        $stmt->execute();
        $role = $stmt->fetch();

        if ($role) {
            $stmt = $pdo->prepare("INSERT INTO UserRole (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$userId, $role['role_id']]);
        }

        // 提交事务
        $pdo->commit();

        sendResponse(0, '注册成功', null);

    } catch (Exception $e) {
        // 回滚事务
        $pdo->rollback();
        sendResponse(1, '注册失败: ' . $e->getMessage());
    }
} else {
    sendResponse(1, '请求方法不正确');
}
?>