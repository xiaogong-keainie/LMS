<?php
// 删除图书接口
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

// 从URL路径中获取book_id
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
preg_match('/\/api\/book\/delete\/(\d+)/', $path, $matches);
$book_id = isset($matches[1]) ? (int)$matches[1] : null;

if (!$book_id) {
    sendResponse(1, '图书ID参数错误');
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $pdo = getConnection();

        // 检查图书是否存在
        $stmt = $pdo->prepare("SELECT book_id FROM Book WHERE book_id = ?");
        $stmt->execute([$book_id]);
        if (!$stmt->fetch()) {
            sendResponse(1, '图书不存在');
        }

        // 检查图书是否有未归还的借阅记录
        $stmt = $pdo->prepare("SELECT borrow_id FROM Borrow WHERE book_id = ? AND return_date IS NULL");
        $stmt->execute([$book_id]);
        if ($stmt->fetch()) {
            sendResponse(1, '图书有未归还的借阅记录，无法删除');
        }

        // 开始事务
        $pdo->beginTransaction();

        // 删除图书-作者关联记录
        $stmt = $pdo->prepare("DELETE FROM BookAuthor WHERE book_id = ?");
        $stmt->execute([$book_id]);

        // 删除借阅记录（已归还的）
        $stmt = $pdo->prepare("DELETE FROM Borrow WHERE book_id = ?");
        $stmt->execute([$book_id]);

        // 删除图书记录
        $stmt = $pdo->prepare("DELETE FROM Book WHERE book_id = ?");
        $result = $stmt->execute([$book_id]);

        if ($result) {
            // 提交事务
            $pdo->commit();
            sendResponse(0, '图书删除成功', null);
        } else {
            // 回滚事务
            $pdo->rollback();
            sendResponse(1, '图书删除失败');
        }

    } catch (Exception $e) {
        // 回滚事务
        $pdo->rollback();
        sendResponse(1, '图书删除失败: ' . $e->getMessage());
    }
} else {
    sendResponse(1, '请求方法不正确');
}
?>