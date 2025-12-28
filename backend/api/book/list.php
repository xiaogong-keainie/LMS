<?php
// 查询图书列表接口
require_once '../../config.php';
require_once '../../utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $pdo = getConnection();

        // 获取查询参数
        $title = isset($_GET['title']) ? trim($_GET['title']) : '';
        $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, (int)$_GET['limit']) : 10; // 限制每页最多100条
        $offset = ($page - 1) * $limit;

        // 构建查询语句
        $query = "
            SELECT 
                b.book_id,
                b.title,
                b.isbn,
                b.category_id,
                c.category_name,
                b.publisher_id,
                p.name as publisher_name,
                b.publish_date,
                b.total_stock,
                b.available_stock
            FROM Book b
            LEFT JOIN Category c ON b.category_id = c.category_id
            LEFT JOIN Publisher p ON b.publisher_id = p.publisher_id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($title)) {
            $query .= " AND b.title LIKE ?";
            $params[] = "%$title%";
        }
        
        if ($category_id > 0) {
            $query .= " AND b.category_id = ?";
            $params[] = $category_id;
        }
        
        $query .= " ORDER BY b.book_id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $books = $stmt->fetchAll();

        // 获取总数用于分页
        $countQuery = "
            SELECT COUNT(*) 
            FROM Book b
            WHERE 1=1
        ";
        $countParams = [];
        
        if (!empty($title)) {
            $countQuery .= " AND b.title LIKE ?";
            $countParams[] = "%$title%";
        }
        
        if ($category_id > 0) {
            $countQuery .= " AND b.category_id = ?";
            $countParams[] = $category_id;
        }
        
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($countParams);
        $total = $countStmt->fetchColumn();
        
        sendResponse(0, '查询成功', [
            'books' => $books,
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