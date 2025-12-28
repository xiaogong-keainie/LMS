<?php
// 新增分类接口
require_once '../../config.php';
require_once '../../utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // 验证输入参数
    if (!validateInput($input, ['category_name'])) {
        sendResponse(1, '缺少必要参数: category_name');
    }

    $category_name = trim($input['category_name']);
    $description = isset($input['description']) ? trim($input['description']) : '';
    $parent_id = isset($input['parent_id']) ? (int)$input['parent_id'] : null;

    try {
        $pdo = getConnection();

        // 检查分类名称是否已存在
        $stmt = $pdo->prepare("SELECT category_id FROM Category WHERE category_name = ?");
        $stmt->execute([$category_name]);
        if ($stmt->fetch()) {
            sendResponse(1, '分类名称已存在');
        }

        // 如果指定了父分类，检查父分类是否存在
        if ($parent_id !== null && $parent_id > 0) {
            $stmt = $pdo->prepare("SELECT category_id FROM Category WHERE category_id = ?");
            $stmt->execute([$parent_id]);
            if (!$stmt->fetch()) {
                sendResponse(1, '父分类不存在');
            }
        }

        // 开始事务
        $pdo->beginTransaction();

        // 插入分类信息
        $stmt = $pdo->prepare("INSERT INTO Category (category_name, description, parent_id) VALUES (?, ?, ?)");
        $result = $stmt->execute([$category_name, $description, $parent_id]);
        
        if ($result) {
            $categoryId = $pdo->lastInsertId();

            // 提交事务
            $pdo->commit();

            sendResponse(0, '分类创建成功', [
                'category_id' => $categoryId
            ]);
        } else {
            // 回滚事务
            $pdo->rollback();
            sendResponse(1, '分类创建失败');
        }

    } catch (Exception $e) {
        // 回滚事务
        $pdo->rollback();
        sendResponse(1, '分类创建失败: ' . $e->getMessage());
    }
} else {
    sendResponse(1, '请求方法不正确');
}
?>