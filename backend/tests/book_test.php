<?php
/**
 * 图书管理模块测试文件
 * 测试图书的增删改查功能
 */

// 引入必要的文件
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils.php';

class BookTest {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    /**
     * 测试创建图书功能
     */
    public function testCreateBook() {
        echo "开始测试创建图书功能...\n";

        // 准备测试数据
        $testBook = [
            'title' => '测试图书' . time(),
            'isbn' => '978-' . rand(1000000000, 9999999999),
            'category_id' => 1, // 假设分类ID 1 存在
            'publisher_id' => 1, // 假设出版社ID 1 存在
            'publish_date' => date('Y-m-d'),
            'total_stock' => 5
        ];

        echo "测试数据: " . json_encode($testBook) . "\n";

        $this->testCreateBookLogic($testBook);

        echo "创建图书功能测试完成\n";
    }

    /**
     * 测试创建图书逻辑
     */
    private function testCreateBookLogic($bookData) {
        try {
            $pdo = $this->pdo;

            // 检查ISBN是否已存在
            $stmt = $pdo->prepare("SELECT book_id FROM Book WHERE isbn = ?");
            $stmt->execute([$bookData['isbn']]);
            $existingBook = $stmt->fetch();

            if ($existingBook) {
                echo "ISBN已存在，跳过创建\n";
                return false;
            }

            // 检查分类是否存在
            $stmt = $pdo->prepare("SELECT category_id FROM Category WHERE category_id = ?");
            $stmt->execute([$bookData['category_id']]);
            if (!$stmt->fetch()) {
                echo "分类不存在，使用默认分类或创建新分类\n";
                // 为测试目的，尝试创建一个分类
                $stmt = $pdo->prepare("INSERT INTO Category (category_name, description) VALUES (?, ?)");
                $result = $stmt->execute(['测试分类', '用于测试的分类']);
                if ($result) {
                    $categoryId = $pdo->lastInsertId();
                    $bookData['category_id'] = $categoryId;
                    echo "创建了新的测试分类，ID: " . $categoryId . "\n";
                }
            }

            // 检查出版社是否存在
            $stmt = $pdo->prepare("SELECT publisher_id FROM Publisher WHERE publisher_id = ?");
            $stmt->execute([$bookData['publisher_id']]);
            if (!$stmt->fetch()) {
                echo "出版社不存在，使用默认出版社或创建新出版社\n";
                // 为测试目的，尝试创建一个出版社
                $stmt = $pdo->prepare("INSERT INTO Publisher (name, address, contact) VALUES (?, ?, ?)");
                $result = $stmt->execute(['测试出版社', '测试地址', '123456789']);
                if ($result) {
                    $publisherId = $pdo->lastInsertId();
                    $bookData['publisher_id'] = $publisherId;
                    echo "创建了新的测试出版社，ID: " . $publisherId . "\n";
                }
            }

            // 开始事务
            $pdo->beginTransaction();

            // 插入图书信息
            $stmt = $pdo->prepare("INSERT INTO Book (isbn, title, category_id, publisher_id, publish_date, total_stock, available_stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $bookData['isbn'], 
                $bookData['title'], 
                $bookData['category_id'], 
                $bookData['publisher_id'], 
                $bookData['publish_date'], 
                $bookData['total_stock'], 
                $bookData['total_stock']
            ]);
            
            if ($result) {
                $bookId = $pdo->lastInsertId();

                // 提交事务
                $pdo->commit();

                echo "图书创建成功，图书ID: " . $bookId . "\n";
                
                // 返回图书ID用于后续测试
                return $bookId;
            } else {
                // 回滚事务
                $pdo->rollback();
                echo "图书创建失败\n";
                return false;
            }

        } catch (Exception $e) {
            // 回滚事务
            $pdo->rollback();
            echo "创建图书过程中发生错误: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试查询图书列表功能
     */
    public function testListBooks() {
        echo "开始测试查询图书列表功能...\n";

        try {
            $pdo = $this->pdo;

            // 查询图书列表
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
                ORDER BY b.book_id DESC 
                LIMIT 10
            ";

            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $books = $stmt->fetchAll();

            echo "查询到 " . count($books) . " 本图书\n";
            if (count($books) > 0) {
                echo "前3本图书信息:\n";
                for ($i = 0; $i < min(3, count($books)); $i++) {
                    echo "  - ID: " . $books[$i]['book_id'] . ", 书名: " . $books[$i]['title'] . ", ISBN: " . $books[$i]['isbn'] . "\n";
                }
            }

            return $books;

        } catch (Exception $e) {
            echo "查询图书列表过程中发生错误: " . $e->getMessage() . "\n";
            return false;
        }

        echo "查询图书列表功能测试完成\n";
    }

    /**
     * 测试更新图书功能
     */
    public function testUpdateBook() {
        echo "开始测试更新图书功能...\n";

        // 首先获取一个已存在的图书进行更新测试
        $pdo = $this->pdo;
        $stmt = $pdo->prepare("SELECT book_id, title FROM Book ORDER BY book_id DESC LIMIT 1");
        $stmt->execute();
        $book = $stmt->fetch();

        if (!$book) {
            echo "没有找到可更新的图书\n";
            return false;
        }

        echo "将要更新的图书: ID=" . $book['book_id'] . ", 书名='" . $book['title'] . "'\n";

        // 准备更新数据
        $updateData = [
            'title' => $book['title'] . '_更新版',
            'publish_date' => date('Y-m-d')
        ];

        try {
            // 更新图书信息
            $setClause = [];
            $params = [];
            foreach ($updateData as $field => $value) {
                $setClause[] = "$field = ?";
                $params[] = $value;
            }
            $params[] = $book['book_id']; // 用于WHERE子句

            $stmt = $pdo->prepare("UPDATE Book SET " . implode(', ', $setClause) . " WHERE book_id = ?");
            $result = $stmt->execute($params);

            if ($result) {
                echo "图书更新成功\n";
                
                // 验证更新结果
                $stmt = $pdo->prepare("SELECT title FROM Book WHERE book_id = ?");
                $stmt->execute([$book['book_id']]);
                $updatedBook = $stmt->fetch();
                echo "更新后的书名: " . $updatedBook['title'] . "\n";
                
                return true;
            } else {
                echo "图书更新失败\n";
                return false;
            }

        } catch (Exception $e) {
            echo "更新图书过程中发生错误: " . $e->getMessage() . "\n";
            return false;
        }

        echo "更新图书功能测试完成\n";
    }

    /**
     * 测试删除图书功能
     */
    public function testDeleteBook() {
        echo "开始测试删除图书功能...\n";

        // 首先创建一本测试图书用于删除
        $testBook = [
            'title' => '待删除测试图书' . time(),
            'isbn' => '978-' . rand(1000000000, 9999999999),
            'category_id' => 1,
            'publisher_id' => 1,
            'publish_date' => date('Y-m-d'),
            'total_stock' => 1
        ];

        // 创建测试图书
        $bookId = $this->testCreateBookLogic($testBook);
        if (!$bookId) {
            echo "无法创建测试图书用于删除测试\n";
            return false;
        }

        echo "创建用于删除测试的图书，ID: " . $bookId . "\n";

        try {
            $pdo = $this->pdo;

            // 检查图书是否存在
            $stmt = $pdo->prepare("SELECT book_id FROM Book WHERE book_id = ?");
            $stmt->execute([$bookId]);
            if (!$stmt->fetch()) {
                echo "图书不存在，无法删除\n";
                return false;
            }

            // 检查图书是否有未归还的借阅记录
            $stmt = $pdo->prepare("SELECT borrow_id FROM Borrow WHERE book_id = ? AND return_date IS NULL");
            $stmt->execute([$bookId]);
            if ($stmt->fetch()) {
                echo "图书有未归还的借阅记录，无法删除\n";
                return false;
            }

            // 开始事务
            $pdo->beginTransaction();

            // 删除图书-作者关联记录
            $stmt = $pdo->prepare("DELETE FROM BookAuthor WHERE book_id = ?");
            $stmt->execute([$bookId]);

            // 删除借阅记录（已归还的）
            $stmt = $pdo->prepare("DELETE FROM Borrow WHERE book_id = ?");
            $stmt->execute([$bookId]);

            // 删除图书记录
            $stmt = $pdo->prepare("DELETE FROM Book WHERE book_id = ?");
            $result = $stmt->execute([$bookId]);

            if ($result) {
                // 提交事务
                $pdo->commit();
                echo "图书删除成功\n";
                return true;
            } else {
                // 回滚事务
                $pdo->rollback();
                echo "图书删除失败\n";
                return false;
            }

        } catch (Exception $e) {
            // 回滚事务
            $pdo->rollback();
            echo "删除图书过程中发生错误: " . $e->getMessage() . "\n";
            return false;
        }

        echo "删除图书功能测试完成\n";
    }

    /**
     * 运行所有测试
     */
    public function runAllTests() {
        echo "开始运行图书管理模块测试...\n";
        echo "==================================\n";

        $this->testCreateBook();
        echo "\n";
        
        $this->testListBooks();
        echo "\n";
        
        $this->testUpdateBook();
        echo "\n";
        
        $this->testDeleteBook();

        echo "==================================\n";
        echo "图书管理模块测试完成!\n";
    }
}

// 创建并运行测试
$test = new BookTest();
$test->runAllTests();
?>