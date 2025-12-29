<?php
// 数据库配置文件
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'liyi20050830');
define('DB_NAME', 'db_big_project');

// 创建数据库连接
function getConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("连接失败: " . $e->getMessage());
    }
}
?>