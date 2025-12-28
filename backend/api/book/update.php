<?php
// 修改图书接口
require_once '../../config.php';
require_once '../../utils.php';

// 从URL路径中获取book_id
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
preg_match('/\/api\/book\/update\/(\d+)/', $path, $matches);
$book_id = isset($matches[1]) ? (int)$matches[1] : null;

if (!$book_id) {
    sendResponse(1, '图书ID参数错误');
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    // 验证至少有一个参数需要更新
    if (empty($input)) {
        sendResponse(1, '请提供需要更新的参数');
    }

    // 检查要更新的字段是否有效
    $allowed_fields = ['title', 'isbn', 'category_id', 'publisher_id', 'publish_date'];
    $update_fields = array_keys($input);
    foreach ($update_fields as $field) {
        if (!in_array($field, $allowed_fields)) {
            sendResponse(1, '不允许更新的字段: ' . $field);
        }
    }

    try {
        $pdo = getConnection();

        // 检查图书是否存在
        $stmt = $pdo->prepare("SELECT book_id FROM Book WHERE book_id = ?");
        $stmt->execute([$book_id]);
        if (!$stmt->fetch()) {
            sendResponse(1, '图书不存在');
        }

        // 检查新的ISBN是否与其他图书冲突
        if (isset($input['isbn'])) {
            $stmt = $pdo->prepare("SELECT book_id FROM Book WHERE isbn = ? AND book_id != ?");
            $stmt->execute([$input['isbn'], $book_id]);
            if ($stmt->fetch()) {
                sendResponse(1, 'ISBN已存在');
            }
        }

        // 检查分类是否存在（如果要更新）
        if (isset($input['category_id'])) {
            $stmt = $pdo->prepare("SELECT category_id FROM Category WHERE category_id = ?");
            $stmt->execute([$input['category_id']]);
            if (!$stmt->fetch()) {
                sendResponse(1, '分类不存在');
            }
        }

        // 检查出版社是否存在（如果要更新）
        if (isset($input['publisher_id'])) {
            $stmt = $pdo->prepare("SELECT publisher_id FROM Publisher WHERE publisher_id = ?");
            $stmt->execute([$input['publisher_id']]);
            if (!$stmt->fetch()) {
                sendResponse(1, '出版社不存在');
            }
        }

        // 构建更新语句
        $update_fields = [];
        $update_values = [];
        foreach ($input as $field => $value) {
            if (in_array($field, $allowed_fields)) {
                $update_fields[] = "$field = ?";
                $update_values[] = $value;
            }
        }

        if (empty($update_fields)) {
            sendResponse(1, '没有有效的更新字段');
        }

        $update_values[] = $book_id; // 用于WHERE子句

        $stmt = $pdo->prepare("UPDATE Book SET " . implode(', ', $update_fields) . " WHERE book_id = ?");
        $result = $stmt->execute($update_values);

        if ($result) {
            sendResponse(0, '图书更新成功', null);
        } else {
            sendResponse(1, '图书更新失败');
        }

    } catch (Exception $e) {
        sendResponse(1, '图书更新失败: ' . $e->getMessage());
    }
} else {
    sendResponse(1, '请求方法不正确');
}
?>