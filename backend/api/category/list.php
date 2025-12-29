<?php
// 查询分类列表接口
$baseDir = dirname(dirname(__DIR__)); // 获取 backend 目录的路径
require_once $baseDir . '/config.php';
require_once $baseDir . '/utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $pdo = getConnection();

        // 获取查询参数
        $category_name = isset($_GET['category_name']) ? trim($_GET['category_name']) : '';
        $parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : -1; // -1表示不筛选父分类
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, (int)$_GET['limit']) : 10; // 限制每页最多100条
        $offset = ($page - 1) * $limit;

        // 构建查询语句
        $query = "
            SELECT
                category_id,
                category_name,
                description,
                parent_id
            FROM Category
            WHERE 1=1
        ";

        $params = [];

        if (!empty($category_name)) {
            $query .= " AND category_name LIKE ?";
            $params[] = "%$category_name%";
        }

        if ($parent_id >= 0) {
            $query .= " AND parent_id = ?";
            $params[] = $parent_id;
        }

        $query .= " ORDER BY category_id DESC LIMIT :limit OFFSET :offset";

        // 使用字符串替换而不是参数绑定，因为LIMIT子句不能使用预处理语句的占位符
        $query = str_replace(':limit', (int)$limit, $query);
        $query = str_replace(':offset', (int)$offset, $query);

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $categories = $stmt->fetchAll();

        // 获取总数用于分页
        $countQuery = "
            SELECT COUNT(*)
            FROM Category
            WHERE 1=1
        ";
        $countParams = [];

        if (!empty($category_name)) {
            $countQuery .= " AND category_name LIKE ?";
            $countParams[] = "%$category_name%";
        }

        if ($parent_id >= 0) {
            $countQuery .= " AND parent_id = ?";
            $countParams[] = $parent_id;
        }

        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($countParams);
        $total = $countStmt->fetchColumn();

        sendResponse(0, '查询成功', [
            'categories' => $categories,
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