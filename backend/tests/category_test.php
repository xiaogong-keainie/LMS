<?php
/**
 * 分类管理模块测试文件
 * 测试分类的增查功能
 */

// 引入必要的文件
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils.php';

class CategoryTest {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    /**
     * 测试创建分类功能
     */
    public function testCreateCategory() {
        echo "开始测试创建分类功能...\n";

        // 准备测试数据
        $testCategory = [
            'category_name' => '测试分类' . time(),
            'description' => '用于测试的分类',
            'parent_id' => null  // 顶级分类
        ];

        echo "测试数据: " . json_encode($testCategory) . "\n";

        $this->testCreateCategoryLogic($testCategory);

        echo "创建分类功能测试完成\n";
    }

    /**
     * 测试创建分类逻辑
     */
    private function testCreateCategoryLogic($categoryData) {
        try {
            $pdo = $this->pdo;

            // 检查分类名称是否已存在
            $stmt = $pdo->prepare("SELECT category_id FROM Category WHERE category_name = ?");
            $stmt->execute([$categoryData['category_name']]);
            $existingCategory = $stmt->fetch();

            if ($existingCategory) {
                echo "分类已存在，跳过创建\n";
                return false;
            }

            // 如果指定了父分类，检查父分类是否存在
            if ($categoryData['parent_id'] !== null && $categoryData['parent_id'] > 0) {
                $stmt = $pdo->prepare("SELECT category_id FROM Category WHERE category_id = ?");
                $stmt->execute([$categoryData['parent_id']]);
                if (!$stmt->fetch()) {
                    echo "父分类不存在，无法创建子分类\n";
                    return false;
                }
            }

            // 开始事务
            $pdo->beginTransaction();

            // 插入分类信息
            $stmt = $pdo->prepare("INSERT INTO Category (category_name, description, parent_id) VALUES (?, ?, ?)");
            $result = $stmt->execute([$categoryData['category_name'], $categoryData['description'], $categoryData['parent_id']]);
            
            if ($result) {
                $categoryId = $pdo->lastInsertId();

                // 提交事务
                $pdo->commit();

                echo "分类创建成功，分类ID: " . $categoryId . "\n";
                
                // 返回分类ID用于后续测试
                return $categoryId;
            } else {
                // 回滚事务
                $pdo->rollback();
                echo "分类创建失败\n";
                return false;
            }

        } catch (Exception $e) {
            // 回滚事务
            $pdo->rollback();
            echo "创建分类过程中发生错误: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 测试查询分类列表功能
     */
    public function testListCategories() {
        echo "开始测试查询分类列表功能...\n";

        try {
            $pdo = $this->pdo;

            // 查询分类列表
            $query = "
                SELECT 
                    category_id,
                    category_name,
                    description,
                    parent_id
                FROM Category
                ORDER BY category_id DESC 
                LIMIT 10
            ";

            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $categories = $stmt->fetchAll();

            echo "查询到 " . count($categories) . " 个分类\n";
            if (count($categories) > 0) {
                echo "前3个分类信息:\n";
                for ($i = 0; $i < min(3, count($categories)); $i++) {
                    echo "  - ID: " . $categories[$i]['category_id'] . ", 名称: " . $categories[$i]['category_name'] . 
                         ", 描述: " . $categories[$i]['description'] . 
                         ", 父分类ID: " . ($categories[$i]['parent_id'] ?? 'NULL') . "\n";
                }
            }

            // 测试按分类名称查询
            echo "\n测试按分类名称查询功能...\n";
            if (count($categories) > 0) {
                $testName = $categories[0]['category_name']; // 使用第一个分类的名称进行测试
                $searchQuery = "
                    SELECT 
                        category_id,
                        category_name,
                        description,
                        parent_id
                    FROM Category
                    WHERE category_name LIKE ?
                    ORDER BY category_id DESC 
                    LIMIT 10
                ";
                
                $searchStmt = $pdo->prepare($searchQuery);
                $searchStmt->execute(["%$testName%"]);
                $searchResults = $searchStmt->fetchAll();
                
                echo "按名称 '$testName' 查询到 " . count($searchResults) . " 个分类\n";
            }

            // 测试查询顶级分类（parent_id为NULL的分类）
            echo "\n测试查询顶级分类功能...\n";
            $topLevelQuery = "
                SELECT 
                    category_id,
                    category_name,
                    description,
                    parent_id
                FROM Category
                WHERE parent_id IS NULL
                ORDER BY category_id DESC 
                LIMIT 10
            ";
            
            $topLevelStmt = $pdo->prepare($topLevelQuery);
            $topLevelStmt->execute();
            $topLevelResults = $topLevelStmt->fetchAll();
            
            echo "查询到 " . count($topLevelResults) . " 个顶级分类\n";

            return $categories;

        } catch (Exception $e) {
            echo "查询分类列表过程中发生错误: " . $e->getMessage() . "\n";
            return false;
        }

        echo "查询分类列表功能测试完成\n";
    }

    /**
     * 运行所有测试
     */
    public function runAllTests() {
        echo "开始运行分类管理模块测试...\n";
        echo "==================================\n";

        $this->testCreateCategory();
        echo "\n";
        
        $this->testListCategories();

        echo "==================================\n";
        echo "分类管理模块测试完成!\n";
    }
}

// 创建并运行测试
$test = new CategoryTest();
$test->runAllTests();
?>