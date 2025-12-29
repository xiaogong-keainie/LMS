<?php
/**
 * 借阅模块测试文件
 * 测试借阅创建、归还和查询功能
 */

// 引入必要的文件
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils.php';

class BorrowTest {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    /**
     * 测试创建借阅记录功能
     */
    public function testCreateBorrow() {
        echo "开始测试创建借阅记录功能...\n";

        // 准备测试数据 - 获取一个存在的用户和图书
        $user = $this->getAvailableUser();
        $book = $this->getAvailableBook();

        if (!$user || !$book) {
            echo "缺少测试数据，无法进行创建借阅测试\n";
            return false;
        }

        echo "使用测试用户ID: " . $user['user_id'] . " (用户名: " . $user['username'] . ")\n";
        echo "使用测试图书ID: " . $book['book_id'] . " (书名: " . $book['title'] . ")\n";

        try {
            $pdo = $this->pdo;
            
            // 开始事务
            $pdo->beginTransaction();
            
            $user_id = $user['user_id'];
            $book_id = $book['book_id'];
            $due_date = date('Y-m-d', strtotime('+7 days'));
            
            // 检查用户是否已借阅此图书（未归还）
            $stmt = $pdo->prepare("SELECT borrow_id FROM Borrow WHERE user_id = ? AND book_id = ? AND status = 'borrowed'");
            $stmt->execute([$user_id, $book_id]);
            if ($stmt->fetch()) {
                echo "用户已借阅此图书，无法重复借阅\n";
                $pdo->rollback();
                return false;
            }
            
            // 获取借阅前的库存
            $stmt = $pdo->prepare("SELECT available_stock FROM Book WHERE book_id = ?");
            $stmt->execute([$book_id]);
            $book_before = $stmt->fetch();
            $available_stock_before = $book_before['available_stock'];
            
            // 创建借阅记录
            $borrow_date = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("INSERT INTO Borrow (user_id, book_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
            $result = $stmt->execute([$user_id, $book_id, $borrow_date, $due_date]);
            
            if ($result) {
                $borrow_id = $pdo->lastInsertId();
                
                // 更新图书可用库存
                $stmt = $pdo->prepare("UPDATE Book SET available_stock = available_stock - 1 WHERE book_id = ?");
                $stmt->execute([$book_id]);
                
                // 提交事务
                $pdo->commit();
                
                // 验证库存是否正确更新
                $stmt = $pdo->prepare("SELECT available_stock FROM Book WHERE book_id = ?");
                $stmt->execute([$book_id]);
                $book_after = $stmt->fetch();
                $available_stock_after = $book_after['available_stock'];
                
                if ($available_stock_after == $available_stock_before - 1) {
                    echo "借阅记录创建成功，借阅ID: " . $borrow_id . "\n";
                    echo "图书库存从 $available_stock_before 更新为 $available_stock_after\n";
                    
                    // 记录借阅ID用于后续测试
                    $this->saveTestBorrowId($borrow_id);
                    return $borrow_id;
                } else {
                    echo "库存更新失败\n";
                    return false;
                }
            } else {
                // 回滚事务
                $pdo->rollback();
                echo "借阅记录创建失败\n";
                return false;
            }
        } catch (Exception $e) {
            // 回滚事务
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollback();
            }
            echo "创建借阅过程中发生错误: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试归还图书功能
     */
    public function testReturnBook() {
        echo "开始测试归还图书功能...\n";

        // 获取一个未归还的借阅记录
        $borrow_id = $this->getTestBorrowId();
        if (!$borrow_id) {
            echo "没有找到待归还的借阅记录\n";
            // 尝试创建一个新的借阅记录用于测试
            $borrow_id = $this->testCreateBorrow();
            if (!$borrow_id) {
                echo "无法创建借阅记录用于归还测试\n";
                return false;
            }
        }

        echo "使用测试借阅ID: " . $borrow_id . "\n";

        try {
            $pdo = $this->pdo;
            
            // 开始事务
            $pdo->beginTransaction();
            
            // 获取借阅记录信息
            $stmt = $pdo->prepare("SELECT user_id, book_id, status FROM Borrow WHERE borrow_id = ?");
            $stmt->execute([$borrow_id]);
            $borrow_record = $stmt->fetch();
            
            if (!$borrow_record) {
                $pdo->rollback();
                echo "借阅记录不存在\n";
                return false;
            }
            
            if ($borrow_record['status'] !== 'borrowed') {
                $pdo->rollback();
                echo "图书已归还或状态异常\n";
                return false;
            }
            
            // 获取归还前的库存
            $stmt = $pdo->prepare("SELECT available_stock FROM Book WHERE book_id = ?");
            $stmt->execute([$borrow_record['book_id']]);
            $book_before = $stmt->fetch();
            $available_stock_before = $book_before['available_stock'];
            
            // 更新借阅记录状态和归还时间
            $return_date = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE Borrow SET status = 'returned', return_date = ? WHERE borrow_id = ?");
            $result = $stmt->execute([$return_date, $borrow_id]);
            
            if ($result) {
                // 更新图书可用库存
                $stmt = $pdo->prepare("UPDATE Book SET available_stock = available_stock + 1 WHERE book_id = ?");
                $stmt->execute([$borrow_record['book_id']]);
                
                // 提交事务
                $pdo->commit();
                
                // 验证库存是否正确更新
                $stmt = $pdo->prepare("SELECT available_stock FROM Book WHERE book_id = ?");
                $stmt->execute([$borrow_record['book_id']]);
                $book_after = $stmt->fetch();
                $available_stock_after = $book_after['available_stock'];
                
                if ($available_stock_after == $available_stock_before + 1) {
                    echo "图书归还成功\n";
                    echo "图书库存从 $available_stock_before 更新为 $available_stock_after\n";
                    return true;
                } else {
                    echo "库存更新失败\n";
                    return false;
                }
            } else {
                // 回滚事务
                $pdo->rollback();
                echo "图书归还失败\n";
                return false;
            }
        } catch (Exception $e) {
            // 回滚事务
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollback();
            }
            echo "归还图书过程中发生错误: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试查询用户借阅记录功能
     */
    public function testQueryUserBorrows() {
        echo "开始测试查询用户借阅记录功能...\n";

        // 获取一个用户
        $user = $this->getAvailableUser();
        if (!$user) {
            echo "没有找到可用的用户进行查询测试\n";
            return false;
        }

        echo "查询用户ID: " . $user['user_id'] . " (用户名: " . $user['username'] . ") 的借阅记录\n";

        try {
            $pdo = $this->pdo;
            
            // 查询用户的借阅记录
            $stmt = $pdo->prepare("
                SELECT 
                    b.borrow_id,
                    b.user_id,
                    u.username,
                    b.book_id,
                    bo.title AS book_title,
                    b.borrow_date,
                    b.due_date,
                    b.return_date,
                    b.status
                FROM Borrow b
                LEFT JOIN User u ON b.user_id = u.user_id
                LEFT JOIN Book bo ON b.book_id = bo.book_id
                WHERE b.user_id = ?
                ORDER BY b.borrow_date DESC
            ");
            $stmt->execute([$user['user_id']]);
            $borrow_records = $stmt->fetchAll();
            
            echo "找到 " . count($borrow_records) . " 条借阅记录\n";
            
            if (count($borrow_records) > 0) {
                foreach ($borrow_records as $record) {
                    echo "  借阅ID: " . $record['borrow_id'] . 
                         ", 图书: " . $record['book_title'] . 
                         ", 状态: " . $record['status'] . 
                         ", 借阅日期: " . $record['borrow_date'] . "\n";
                }
            }
            
            return true;
        } catch (Exception $e) {
            echo "查询用户借阅记录过程中发生错误: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 获取一个可用的用户
     */
    private function getAvailableUser() {
        try {
            $stmt = $this->pdo->query("SELECT user_id, username FROM User LIMIT 1");
            return $stmt->fetch();
        } catch (Exception $e) {
            echo "获取用户时发生错误: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 获取一本有库存的图书
     */
    private function getAvailableBook() {
        try {
            $stmt = $this->pdo->query("SELECT book_id, title FROM Book WHERE available_stock > 0 LIMIT 1");
            return $stmt->fetch();
        } catch (Exception $e) {
            echo "获取图书时发生错误: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 保存测试借阅ID到临时文件
     */
    private function saveTestBorrowId($borrow_id) {
        file_put_contents(__DIR__ . '/.borrow_test_id', $borrow_id);
    }

    /**
     * 从临时文件获取测试借阅ID
     */
    private function getTestBorrowId() {
        if (file_exists(__DIR__ . '/.borrow_test_id')) {
            return file_get_contents(__DIR__ . '/.borrow_test_id');
        }
        return false;
    }

    /**
     * 运行所有测试
     */
    public function runAllTests() {
        echo "开始运行借阅模块测试...\n";
        echo "==================================\n";

        $this->testQueryUserBorrows();
        echo "\n";
        
        $borrow_id = $this->testCreateBorrow();
        echo "\n";
        
        if ($borrow_id) {
            // 重新查询以验证新创建的借阅记录
            $this->testQueryUserBorrows();
            echo "\n";
            
            $this->testReturnBook();
            echo "\n";
            
            // 再次查询以验证归还后的状态
            $this->testQueryUserBorrows();
        }

        echo "==================================\n";
        echo "借阅模块测试完成!\n";
    }
}

// 创建并运行测试
$test = new BorrowTest();
$test->runAllTests();
?>