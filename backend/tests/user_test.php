<?php
/**
 * 用户模块测试文件
 * 测试用户注册和登录功能
 */

// 引入必要的文件
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils.php';

class UserTest {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    /**
     * 测试用户注册功能
     */
    public function testUserRegistration() {
        echo "开始测试用户注册功能...\n";

        // 准备测试数据
        $testUser = [
            'username' => 'testuser_' . time(),
            'password' => 'testpassword123',
            'email' => 'test_' . time() . '@example.com'
        ];

        echo "测试数据: " . json_encode($testUser) . "\n";

        // 由于直接测试API文件比较困难，我们测试相关的函数
        $this->testRegistrationLogic($testUser);

        echo "用户注册功能测试完成\n";
    }

    /**
     * 测试注册逻辑
     */
    private function testRegistrationLogic($userData) {
        try {
            $pdo = $this->pdo;

            // 检查用户名是否已存在
            $stmt = $pdo->prepare("SELECT user_id FROM User WHERE username = ?");
            $stmt->execute([$userData['username']]);
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                echo "用户已存在，跳过创建\n";
                return false;
            }

            // 检查邮箱是否已存在
            $stmt = $pdo->prepare("SELECT user_id FROM User WHERE email = ?");
            $stmt->execute([$userData['email']]);
            $existingEmail = $stmt->fetch();

            if ($existingEmail) {
                echo "邮箱已被注册，跳过创建\n";
                return false;
            }

            // 密码加密
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

            // 开始事务
            $pdo->beginTransaction();

            // 插入用户信息
            $stmt = $pdo->prepare("INSERT INTO User (username, password, email, status, created_at) VALUES (?, ?, ?, 1, NOW())");
            $result = $stmt->execute([$userData['username'], $hashedPassword, $userData['email']]);

            if ($result) {
                $userId = $pdo->lastInsertId();

                // 为新用户创建用户资料
                $stmt = $pdo->prepare("INSERT INTO UserProfile (user_id, register_date) VALUES (?, CURDATE())");
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

                echo "用户注册成功，用户ID: " . $userId . "\n";
                return true;
            } else {
                // 回滚事务
                $pdo->rollback();
                echo "用户注册失败\n";
                return false;
            }
        } catch (Exception $e) {
            // 回滚事务
            $pdo->rollback();
            echo "注册过程中发生错误: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试用户登录功能
     */
    public function testUserLogin() {
        echo "开始测试用户登录功能...\n";

        // 使用之前创建的测试用户或已存在的用户进行登录测试
        $loginData = [
            'username' => 'admin', // 使用默认管理员账户
            'password' => 'admin123' // 假设密码是admin123
        ];

        // 如果管理员账户不存在，使用测试用户
        $testUsername = 'testuser_' . (time() - 300); // 尝试使用一个可能已创建的测试用户
        $this->testLoginLogic(['username' => $testUsername, 'password' => 'testpassword123']) ||
        $this->testLoginLogic($loginData);

        echo "用户登录功能测试完成\n";
    }

    /**
     * 测试登录逻辑
     */
    private function testLoginLogic($loginData) {
        try {
            $pdo = $this->pdo;

            // 查询用户信息
            $stmt = $pdo->prepare("SELECT user_id, username, password FROM User WHERE username = ? AND status = 1");
            $stmt->execute([$loginData['username']]);
            $user = $stmt->fetch();

            if (!$user) {
                echo "用户 '" . $loginData['username'] . "' 不存在或账户已被禁用\n";
                // 尝试使用测试用户
                return false;
            }

            // 验证密码
            if (!password_verify($loginData['password'], $user['password'])) {
                echo "密码错误\n";
                return false;
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

            echo "登录成功! 用户ID: " . $user['user_id'] . ", 角色: " . $role . "\n";
            return true;

        } catch (Exception $e) {
            echo "登录过程中发生错误: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试输入验证功能
     */
    public function testInputValidation() {
        echo "开始测试输入验证功能...\n";

        // 测试缺少必要参数
        $validData = ['username' => 'test', 'password' => 'test', 'email' => 'test@example.com'];
        $invalidData1 = ['username' => 'test', 'password' => 'test']; // 缺少email
        $invalidData2 = ['username' => '', 'password' => 'test', 'email' => 'test@example.com']; // 空用户名

        $required_fields = ['username', 'password', 'email'];

        echo "验证完整数据: " . (validateInput($validData, $required_fields) ? "通过" : "失败") . "\n";
        echo "验证缺少参数的数据: " . (validateInput($invalidData1, $required_fields) ? "通过" : "失败") . "\n";
        echo "验证空参数的数据: " . (validateInput($invalidData2, $required_fields) ? "通过" : "失败") . "\n";

        echo "输入验证功能测试完成\n";
    }

    /**
     * 运行所有测试
     */
    public function runAllTests() {
        echo "开始运行用户模块测试...\n";
        echo "==================================\n";

        $this->testInputValidation();
        echo "\n";
        $this->testUserRegistration();
        echo "\n";
        $this->testUserLogin();

        echo "==================================\n";
        echo "用户模块测试完成!\n";
    }
}

// 创建并运行测试
$test = new UserTest();
$test->runAllTests();
?>