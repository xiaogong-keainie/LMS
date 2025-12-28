<?php
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

// 获取POST请求体
$input = json_decode(file_get_contents('php://input'), true);

// 验证必需参数
$required_fields = ['user_id', 'book_id', 'due_date'];
if (!validateInput($input, $required_fields)) {
    sendResponse(1, '缺少必需参数: user_id, book_id, due_date', null);
}

try {
    $pdo = getConnection();

    // 开始事务
    $pdo->beginTransaction();

    $user_id = $input['user_id'];
    $book_id = $input['book_id'];
    $due_date = $input['due_date'];

    // 检查用户是否存在
    $stmt = $pdo->prepare("SELECT user_id FROM User WHERE user_id = ?");
    $stmt->execute([$user_id]);
    if (!$stmt->fetch()) {
        $pdo->rollback();
        sendResponse(1, '用户不存在', null);
    }

    // 检查图书是否存在且有库存
    $stmt = $pdo->prepare("SELECT available_stock FROM Book WHERE book_id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();
    if (!$book) {
        $pdo->rollback();
        sendResponse(1, '图书不存在', null);
    }

    if ($book['available_stock'] <= 0) {
        $pdo->rollback();
        sendResponse(1, '图书库存不足', null);
    }

    // 检查用户是否已借阅此图书（未归还）
    $stmt = $pdo->prepare("SELECT borrow_id FROM Borrow WHERE user_id = ? AND book_id = ? AND status = 'borrowed'");
    $stmt->execute([$user_id, $book_id]);
    if ($stmt->fetch()) {
        $pdo->rollback();
        sendResponse(1, '用户已借阅此图书，无法重复借阅', null);
    }

    // 获取当前时间作为借阅日期
    $borrow_date = date('Y-m-d H:i:s');

    // 创建借阅记录
    $stmt = $pdo->prepare("INSERT INTO Borrow (user_id, book_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
    $stmt->execute([$user_id, $book_id, $borrow_date, $due_date]);
    $borrow_id = $pdo->lastInsertId();

    // 更新图书可用库存
    $stmt = $pdo->prepare("UPDATE Book SET available_stock = available_stock - 1 WHERE book_id = ?");
    $stmt->execute([$book_id]);

    // 提交事务
    $pdo->commit();

    sendResponse(0, '借阅记录创建成功', ['borrow_id' => $borrow_id]);

} catch (Exception $e) {
    // 回滚事务
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    sendResponse(1, '创建借阅记录失败: ' . $e->getMessage(), null);
}
?>