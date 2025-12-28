<?php
/**
 * 作者管理模块测试文件
 * 测试作者的增查功能
 */

// 引入必要的文件
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils.php';

class AuthorTest {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    /**
     * 测试创建作者功能
     */
    public function testCreateAuthor() {
        echo "开始测试创建作者功能...\n";

        // 准备测试数据
        $testAuthor = [
            'name' => '测试作者' . time(),
            'country' => '测试国家'
        ];

        echo "测试数据: " . json_encode($testAuthor) . "\n";

        $this->testCreateAuthorLogic($testAuthor);

        echo "创建作者功能测试完成\n";
    }

    /**
     * 测试创建作者逻辑
     */
    private function testCreateAuthorLogic($authorData) {
        try {
            $pdo = $this->pdo;

            // 检查作者是否已存在（基于姓名）
            $stmt = $pdo->prepare("SELECT author_id FROM Author WHERE name = ?");
            $stmt->execute([$authorData['name']]);
            $existingAuthor = $stmt->fetch();

            if ($existingAuthor) {
                echo "作者已存在，跳过创建\n";
                return false;
            }

            // 开始事务
            $pdo->beginTransaction();

            // 插入作者信息
            $stmt = $pdo->prepare("INSERT INTO Author (name, country) VALUES (?, ?)");
            $result = $stmt->execute([$authorData['name'], $authorData['country']]);
            
            if ($result) {
                $authorId = $pdo->lastInsertId();

                // 提交事务
                $pdo->commit();

                echo "作者创建成功，作者ID: " . $authorId . "\n";
                
                // 返回作者ID用于后续测试
                return $authorId;
            } else {
                // 回滚事务
                $pdo->rollback();
                echo "作者创建失败\n";
                return false;
            }

        } catch (Exception $e) {
            // 回滚事务
            $pdo->rollback();
            echo "创建作者过程中发生错误: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试查询作者列表功能
     */
    public function testListAuthors() {
        echo "开始测试查询作者列表功能...\n";

        try {
            $pdo = $this->pdo;

            // 查询作者列表
            $query = "
                SELECT 
                    author_id,
                    name,
                    country
                FROM Author
                ORDER BY author_id DESC 
                LIMIT 10
            ";

            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $authors = $stmt->fetchAll();

            echo "查询到 " . count($authors) . " 位作者\n";
            if (count($authors) > 0) {
                echo "前3位作者信息:\n";
                for ($i = 0; $i < min(3, count($authors)); $i++) {
                    echo "  - ID: " . $authors[$i]['author_id'] . ", 姓名: " . $authors[$i]['name'] . ", 国家: " . $authors[$i]['country'] . "\n";
                }
            }

            // 测试按姓名查询
            echo "\n测试按姓名查询功能...\n";
            if (count($authors) > 0) {
                $testName = $authors[0]['name']; // 使用第一位作者的姓名进行测试
                $searchQuery = "
                    SELECT 
                        author_id,
                        name,
                        country
                    FROM Author
                    WHERE name LIKE ?
                    ORDER BY author_id DESC 
                    LIMIT 10
                ";
                
                $searchStmt = $pdo->prepare($searchQuery);
                $searchStmt->execute(["%$testName%"]);
                $searchResults = $searchStmt->fetchAll();
                
                echo "按姓名 '$testName' 查询到 " . count($searchResults) . " 位作者\n";
            }

            return $authors;

        } catch (Exception $e) {
            echo "查询作者列表过程中发生错误: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 运行所有测试
     */
    public function runAllTests() {
        echo "开始运行作者管理模块测试...\n";
        echo "==================================\n";

        $this->testCreateAuthor();
        echo "\n";
        
        $this->testListAuthors();

        echo "==================================\n";
        echo "作者管理模块测试完成!\n";
    }
}

// 创建并运行测试
$test = new AuthorTest();
$test->runAllTests();
?>