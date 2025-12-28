<?php
// 新增图书接口
require_once '../../config.php';
require_once '../../utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // 验证输入参数
    if (!validateInput($input, ['title', 'isbn', 'category_id', 'publisher_id'])) {
        sendResponse(1, '缺少必要参数');
    }

    $title = trim($input['title']);
    $isbn = trim($input['isbn']);
    $category_id = $input['category_id'];
    $publisher_id = $input['publisher_id'];
    $publish_date = isset($input['publish_date']) ? $input['publish_date'] : null;
    $total_stock = isset($input['total_stock']) ? (int)$input['total_stock'] : 1; // 默认库存为1

    try {
        $pdo = getConnection();

        // 检查ISBN是否已存在
        $stmt = $pdo->prepare("SELECT book_id FROM Book WHERE isbn = ?");
        $stmt->execute([$isbn]);
        if ($stmt->fetch()) {
            sendResponse(1, 'ISBN已存在');
        }

        // 检查分类是否存在
        $stmt = $pdo->prepare("SELECT category_id FROM Category WHERE category_id = ?");
        $stmt->execute([$category_id]);
        if (!$stmt->fetch()) {
            sendResponse(1, '分类不存在');
        }

        // 检查出版社是否存在
        $stmt = $pdo->prepare("SELECT publisher_id FROM Publisher WHERE publisher_id = ?");
        $stmt->execute([$publisher_id]);
        if (!$stmt->fetch()) {
            sendResponse(1, '出版社不存在');
        }

        // 开始事务
        $pdo->beginTransaction();

        // 插入图书信息
        $stmt = $pdo->prepare("INSERT INTO Book (isbn, title, category_id, publisher_id, publish_date, total_stock, available_stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$isbn, $title, $category_id, $publisher_id, $publish_date, $total_stock, $total_stock]);
        
        if ($result) {
            $bookId = $pdo->lastInsertId();

            // 提交事务
            $pdo->commit();

            sendResponse(0, '图书创建成功', [
                'book_id' => $bookId
            ]);
        } else {
            // 回滚事务
            $pdo->rollback();
            sendResponse(1, '图书创建失败');
        }

    } catch (Exception $e) {
        // 回滚事务
        $pdo->rollback();
        sendResponse(1, '图书创建失败: ' . $e->getMessage());
    }
} else {
    sendResponse(1, '请求方法不正确');
}
?>