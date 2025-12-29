<?php
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

// 获取路径参数
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
preg_match('/^\/api\/borrow\/return\/(\d+)$/', $path, $matches);
if (!isset($matches[1])) {
    sendResponse(1, '借阅ID参数错误', null);
}
$borrow_id = $matches[1];

try {
    $pdo = getConnection();

    // 开始事务
    $pdo->beginTransaction();

    // 获取借阅记录信息
    $stmt = $pdo->prepare("SELECT user_id, book_id, status FROM Borrow WHERE borrow_id = ?");
    $stmt->execute([$borrow_id]);
    $borrow_record = $stmt->fetch();

    if (!$borrow_record) {
        $pdo->rollback();
        sendResponse(1, '借阅记录不存在', null);
    }

    if ($borrow_record['status'] !== 'borrowed') {
        $pdo->rollback();
        sendResponse(1, '图书已归还或状态异常', null);
    }

    // 更新借阅记录状态和归还时间
    $return_date = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("UPDATE Borrow SET status = 'returned', return_date = ? WHERE borrow_id = ?");
    $stmt->execute([$return_date, $borrow_id]);

    // 更新图书可用库存
    $stmt = $pdo->prepare("UPDATE Book SET available_stock = available_stock + 1 WHERE book_id = ?");
    $stmt->execute([$borrow_record['book_id']]);

    // 提交事务
    $pdo->commit();

    sendResponse(0, '图书归还成功', null);

} catch (Exception $e) {
    // 回滚事务
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    sendResponse(1, '图书归还失败: ' . $e->getMessage(), null);
}
?>