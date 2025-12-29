<?php
require_once 'config.php';
require_once 'utils.php';

try {
    $pdo = getConnection();
    echo "数据库连接成功！";
} catch (Exception $e) {
    echo "数据库连接失败: " . $e->getMessage();
}
?>