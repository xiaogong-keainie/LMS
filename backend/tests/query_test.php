<?php
/**
 * 查询模块测试文件
 * 测试各种SQL查询功能
 */

// 引入必要的文件
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils.php';

class QueryTest {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    /**
     * 测试连接查询（Inner/Outer Join）
     */
    public function testJoinQuery() {
        echo "开始测试连接查询（Book-Authors）...\n";

        try {
            $pdo = $this->pdo;
            
            $stmt = $pdo->prepare("
                SELECT 
                    b.book_id,
                    b.title AS book_title,
                    a.author_id,
                    a.name AS author_name,
                    a.country
                FROM Book b
                INNER JOIN BookAuthor ba ON b.book_id = ba.book_id
                INNER JOIN Author a ON ba.author_id = a.author_id
                LIMIT 5
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            echo "找到 " . count($results) . " 条图书-作者关联记录\n";
            foreach ($results as $record) {
                echo "  图书: " . $record['book_title'] . 
                     " (ID: " . $record['book_id'] . 
                     ") - 作者: " . $record['author_name'] . 
                     " (ID: " . $record['author_id'] . ")\n";
            }
            
            return true;
        } catch (Exception $e) {
            echo "连接查询测试失败: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试自连接查询
     */
    public function testSelfJoinQuery() {
        echo "开始测试自连接查询（Category Tree）...\n";

        try {
            $pdo = $this->pdo;
            
            $stmt = $pdo->prepare("
                SELECT 
                    c1.category_id,
                    c1.category_name,
                    c1.parent_id AS parent_category_id,
                    c2.category_name AS parent_category_name
                FROM Category c1
                LEFT JOIN Category c2 ON c1.parent_id = c2.category_id
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            echo "找到 " . count($results) . " 条分类记录\n";
            foreach ($results as $record) {
                $parent = $record['parent_category_name'] ?? '无';
                echo "  分类: " . $record['category_name'] . 
                     " (ID: " . $record['category_id'] . 
                     ") - 父分类: " . $parent . "\n";
            }
            
            return true;
        } catch (Exception $e) {
            echo "自连接查询测试失败: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试聚合查询
     */
    public function testAggregateQuery() {
        echo "开始测试聚合查询（Book Count by Category）...\n";

        try {
            $pdo = $this->pdo;
            
            $stmt = $pdo->prepare("
                SELECT 
                    c.category_id,
                    c.category_name,
                    COUNT(b.book_id) AS book_count
                FROM Category c
                LEFT JOIN Book b ON c.category_id = b.category_id
                GROUP BY c.category_id, c.category_name
                ORDER BY book_count DESC
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            echo "找到 " . count($results) . " 个分类的图书统计\n";
            foreach ($results as $record) {
                echo "  分类: " . $record['category_name'] . 
                     " (ID: " . $record['category_id'] . 
                     ") - 图书数量: " . $record['book_count'] . "\n";
            }
            
            return true;
        } catch (Exception $e) {
            echo "聚合查询测试失败: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试日期与时间函数查询
     */
    public function testDateFunctionQuery() {
        echo "开始测试日期与时间函数查询（Overdue Borrow）...\n";

        try {
            $pdo = $this->pdo;
            
            $stmt = $pdo->prepare("
                SELECT 
                    b.borrow_id,
                    b.user_id,
                    u.username,
                    b.book_id,
                    bo.title AS book_title,
                    b.borrow_date,
                    b.due_date,
                    DATEDIFF(NOW(), b.due_date) AS days_overdue
                FROM Borrow b
                INNER JOIN User u ON b.user_id = u.user_id
                INNER JOIN Book bo ON b.book_id = bo.book_id
                WHERE b.status = 'borrowed' AND b.due_date < NOW()
                ORDER BY days_overdue DESC
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            if (count($results) > 0) {
                echo "找到 " . count($results) . " 条逾期借阅记录\n";
                foreach ($results as $record) {
                    echo "  借阅ID: " . $record['borrow_id'] . 
                         ", 用户: " . $record['username'] . 
                         ", 图书: " . $record['book_title'] . 
                         ", 逾期天数: " . $record['days_overdue'] . "\n";
                }
            } else {
                echo "没有找到逾期借阅记录\n";
            }
            
            return true;
        } catch (Exception $e) {
            echo "日期函数查询测试失败: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试子查询
     */
    public function testSubquery() {
        echo "开始测试子查询（Users Borrowed Book）...\n";

        // 获取一本已知的图书
        $stmt = $this->pdo->query("SELECT book_id, title FROM Book LIMIT 1");
        $book = $stmt->fetch();
        
        if (!$book) {
            echo "没有找到图书数据\n";
            return false;
        }

        try {
            $pdo = $this->pdo;
            
            $stmt = $pdo->prepare("
                SELECT 
                    u.user_id,
                    u.username,
                    b.borrow_date
                FROM User u
                INNER JOIN Borrow b ON u.user_id = b.user_id
                WHERE b.book_id = ?
                ORDER BY b.borrow_date DESC
            ");
            $stmt->execute([$book['book_id']]);
            $results = $stmt->fetchAll();
            
            echo "图书 '" . $book['title'] . "' (ID: " . $book['book_id'] . ") 被 " . 
                 count($results) . " 个用户借阅过\n";
                 
            foreach ($results as $record) {
                echo "  用户: " . $record['username'] . 
                     " (ID: " . $record['user_id'] . 
                     ") - 借阅日期: " . $record['borrow_date'] . "\n";
            }
            
            return true;
        } catch (Exception $e) {
            echo "子查询测试失败: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试相关子查询
     */
    public function testCorrelatedSubquery() {
        echo "开始测试相关子查询（Most Active Users）...\n";

        try {
            $pdo = $this->pdo;
            
            $stmt = $pdo->prepare("
                SELECT 
                    u.user_id,
                    u.username,
                    COUNT(b.borrow_id) AS borrow_count
                FROM User u
                INNER JOIN Borrow b ON u.user_id = b.user_id
                GROUP BY u.user_id, u.username
                HAVING COUNT(b.borrow_id) = (
                    SELECT MAX(borrow_counts.cnt)
                    FROM (
                        SELECT COUNT(borrow_id) AS cnt
                        FROM Borrow
                        GROUP BY user_id
                    ) AS borrow_counts
                )
                ORDER BY borrow_count DESC
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            // 如果上面的查询没有结果，使用替代查询
            if (empty($results)) {
                $stmt = $pdo->prepare("
                    SELECT 
                        u.user_id,
                        u.username,
                        COUNT(b.borrow_id) AS borrow_count
                    FROM User u
                    INNER JOIN Borrow b ON u.user_id = b.user_id
                    GROUP BY u.user_id, u.username
                    ORDER BY borrow_count DESC
                    LIMIT 5
                ");
                $stmt->execute();
                $results = $stmt->fetchAll();
            }
            
            echo "找到 " . count($results) . " 个活跃用户\n";
            foreach ($results as $record) {
                echo "  用户: " . $record['username'] . 
                     " (ID: " . $record['user_id'] . 
                     ") - 借阅次数: " . $record['borrow_count'] . "\n";
            }
            
            return true;
        } catch (Exception $e) {
            echo "相关子查询测试失败: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试集合运算
     */
    public function testSetOperation() {
        echo "开始测试集合运算（Active or Admin Users）...\n";

        try {
            $pdo = $this->pdo;
            
            $stmt = $pdo->prepare("
                SELECT DISTINCT
                    u.user_id,
                    u.username,
                    r.role_name AS role
                FROM User u
                INNER JOIN UserRole ur ON u.user_id = ur.user_id
                INNER JOIN Role r ON ur.role_id = r.role_id
                WHERE r.role_name = 'ADMIN'
                UNION
                SELECT DISTINCT
                    u.user_id,
                    u.username,
                    'READER' AS role
                FROM User u
                INNER JOIN Borrow b ON u.user_id = b.user_id
                WHERE b.status = 'borrowed'
                ORDER BY user_id
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            echo "找到 " . count($results) . " 个活跃用户或管理员\n";
            foreach ($results as $record) {
                echo "  用户: " . $record['username'] . 
                     " (ID: " . $record['user_id'] . 
                     ") - 角色: " . $record['role'] . "\n";
            }
            
            return true;
        } catch (Exception $e) {
            echo "集合运算查询测试失败: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试多表连接查询
     */
    public function testMultiTableJoin() {
        echo "开始测试多表连接查询（Borrow Detail）...\n";

        try {
            $pdo = $this->pdo;
            
            $stmt = $pdo->prepare("
                SELECT 
                    b.borrow_id,
                    b.user_id,
                    u.username,
                    b.book_id,
                    bo.title AS book_title,
                    GROUP_CONCAT(a.name SEPARATOR ', ') AS author_names,
                    c.category_name,
                    b.borrow_date,
                    b.due_date,
                    b.return_date,
                    b.status
                FROM Borrow b
                INNER JOIN User u ON b.user_id = u.user_id
                INNER JOIN Book bo ON b.book_id = bo.book_id
                INNER JOIN Category c ON bo.category_id = c.category_id
                LEFT JOIN BookAuthor ba ON bo.book_id = ba.book_id
                LEFT JOIN Author a ON ba.author_id = a.author_id
                GROUP BY b.borrow_id, b.user_id, u.username, b.book_id, bo.title, 
                         c.category_name, b.borrow_date, b.due_date, b.return_date, b.status
                ORDER BY b.borrow_date DESC
                LIMIT 10
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            echo "找到 " . count($results) . " 条借阅详情记录\n";
            foreach ($results as $record) {
                $authors = $record['author_names'] ?? '未知';
                $return_date = $record['return_date'] ?? '未归还';
                echo "  借阅ID: " . $record['borrow_id'] . 
                     ", 用户: " . $record['username'] . 
                     ", 图书: " . $record['book_title'] . 
                     ", 作者: " . $authors .
                     ", 分类: " . $record['category_name'] .
                     ", 状态: " . $record['status'] . "\n";
            }
            
            return true;
        } catch (Exception $e) {
            echo "多表连接查询测试失败: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试除法查询
     */
    public function testDivisionQuery() {
        echo "开始测试除法查询（Users Borrowed All Categories）...\n";

        try {
            $pdo = $this->pdo;
            
            $stmt = $pdo->prepare("
                SELECT 
                    u.user_id,
                    u.username
                FROM User u
                WHERE NOT EXISTS (
                    SELECT c.category_id
                    FROM Category c
                    WHERE c.category_id IS NOT NULL
                    AND NOT EXISTS (
                        SELECT 1
                        FROM Borrow b
                        INNER JOIN Book bo ON b.book_id = bo.book_id
                        WHERE b.user_id = u.user_id
                        AND bo.category_id = c.category_id
                    )
                )
                AND u.user_id IN (
                    SELECT DISTINCT b.user_id
                    FROM Borrow b
                    INNER JOIN Book bo ON b.book_id = bo.book_id
                    WHERE bo.category_id IS NOT NULL
                )
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            if (count($results) > 0) {
                echo "找到 " . count($results) . " 个借阅过所有分类图书的用户\n";
                foreach ($results as $record) {
                    echo "  用户: " . $record['username'] . 
                         " (ID: " . $record['user_id'] . ")\n";
                }
            } else {
                echo "没有找到借阅过所有分类图书的用户\n";
            }
            
            return true;
        } catch (Exception $e) {
            echo "除法查询测试失败: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 运行所有测试
     */
    public function runAllTests() {
        echo "开始运行查询模块测试...\n";
        echo "==================================\n";

        $this->testJoinQuery();
        echo "\n";
        
        $this->testSelfJoinQuery();
        echo "\n";
        
        $this->testAggregateQuery();
        echo "\n";
        
        $this->testDateFunctionQuery();
        echo "\n";
        
        $this->testSubquery();
        echo "\n";
        
        $this->testCorrelatedSubquery();
        echo "\n";
        
        $this->testSetOperation();
        echo "\n";
        
        $this->testMultiTableJoin();
        echo "\n";
        
        $this->testDivisionQuery();

        echo "==================================\n";
        echo "查询模块测试完成!\n";
    }
}

// 创建并运行测试
$test = new QueryTest();
$test->runAllTests();
?>