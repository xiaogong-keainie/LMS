<?php
// 用户登录接口
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // 验证输入参数
    if (!validateInput($input, ['username', 'password'])) {
        sendResponse(1, '缺少必要参数');
    }

    $username = trim($input['username']);
    $password = trim($input['password']);

    try {
        $pdo = getConnection();

        // 查询用户信息
        $stmt = $pdo->prepare("SELECT user_id, username, password FROM User WHERE username = ? AND status = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            sendResponse(1, '用户名不存在或账户已被禁用');
        }

        // 验证密码
        if (!password_verify($password, $user['password'])) {
            sendResponse(1, '密码错误');
        }

        // 获取用户角色
        $stmt = $pdo->prepare("
            SELECT r.role_name
            FROM UserRole ur
            JOIN Role r ON ur.role_id = r.role_id
            WHERE ur.user_id = ?
        ");
        $stmt->execute([$user['user_id']]);
        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $role = !empty($roles) ? $roles[0] : 'READER'; // 默认为READER

        sendResponse(0, '登录成功', [
            'user_id' => $user['user_id'],
            'role' => $role
        ]);

    } catch (Exception $e) {
        sendResponse(1, '登录失败: ' . $e->getMessage());
    }
} else {
    sendResponse(1, '请求方法不正确');
}
?>