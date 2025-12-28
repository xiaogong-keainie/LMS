<?php
// 查询作者列表接口
require_once '../../config.php';
require_once '../../utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $pdo = getConnection();

        // 获取查询参数
        $name = isset($_GET['name']) ? trim($_GET['name']) : '';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, (int)$_GET['limit']) : 10; // 限制每页最多100条
        $offset = ($page - 1) * $limit;

        // 构建查询语句
        $query = "
            SELECT 
                author_id,
                name,
                country
            FROM Author
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($name)) {
            $query .= " AND name LIKE ?";
            $params[] = "%$name%";
        }
        
        $query .= " ORDER BY author_id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $authors = $stmt->fetchAll();

        // 获取总数用于分页
        $countQuery = "
            SELECT COUNT(*) 
            FROM Author
            WHERE 1=1
        ";
        $countParams = [];
        
        if (!empty($name)) {
            $countQuery .= " AND name LIKE ?";
            $countParams[] = "%$name%";
        }
        
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($countParams);
        $total = $countStmt->fetchColumn();
        
        sendResponse(0, '查询成功', [
            'authors' => $authors,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);

    } catch (Exception $e) {
        sendResponse(1, '查询失败: ' . $e->getMessage());
    }
} else {
    sendResponse(1, '请求方法不正确');
}
?>