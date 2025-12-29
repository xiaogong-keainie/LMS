<template>
  <div class="list-container">
    <h2>数据查询与统计模块</h2>

    <!-- 仅管理员显示统计信息 -->
    <div v-if="isAdmin">
      <!-- 按分类统计图书数量 -->
      <h3 style="color: #2c3e50; margin: 25px 0 15px;">按分类统计图书数量</h3>
      <table>
        <thead>
          <tr>
            <th>分类</th>
            <th>图书数量</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in bookCountByCategory" :key="item.category_id">
            <td>{{ item.category_name }}</td>
            <td>{{ item.book_count }}</td>
          </tr>
        </tbody>
      </table>

      <!-- 最活跃用户 -->
      <h3 style="color: #2c3e50; margin: 35px 0 15px;">最活跃用户</h3>
      <table>
        <thead>
          <tr>
            <th>用户名</th>
            <th>借阅次数</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in mostActiveUsers" :key="user.user_id">
            <td>{{ user.username }}</td>
            <td>{{ user.borrow_count }}</td>
          </tr>
        </tbody>
      </table>

      <!-- 图书与作者连接查询 -->
      <h3 style="color: #2c3e50; margin: 35px 0 15px;">图书与作者信息</h3>
      <div class="search-section">
        <input 
          v-model="searchBookId" 
          type="number" 
          placeholder="输入图书ID查询（可选）" 
          class="search-input"
        />
        <button @click="fetchBookAuthors" class="action-button">查询图书作者</button>
      </div>
      <table>
        <thead>
          <tr>
            <th>图书ID</th>
            <th>图书标题</th>
            <th>作者ID</th>
            <th>作者姓名</th>
            <th>国籍</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in bookAuthors" :key="`${item.book_id}-${item.author_id}`">
            <td>{{ item.book_id }}</td>
            <td>{{ item.book_title }}</td>
            <td>{{ item.author_id }}</td>
            <td>{{ item.author_name }}</td>
            <td>{{ item.country }}</td>
          </tr>
        </tbody>
      </table>

      <!-- 分类树（自连接查询） -->
      <h3 style="color: #2c3e50; margin: 35px 0 15px;">分类树结构</h3>
      <table>
        <thead>
          <tr>
            <th>分类ID</th>
            <th>分类名称</th>
            <th>父分类ID</th>
            <th>父分类名称</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in categoryTree" :key="item.category_id">
            <td>{{ item.category_id }}</td>
            <td>{{ item.category_name }}</td>
            <td>{{ item.parent_category_id || '-' }}</td>
            <td>{{ item.parent_category_name || '-' }}</td>
          </tr>
        </tbody>
      </table>

      <!-- 逾期借阅查询 -->
      <h3 style="color: #2c3e50; margin: 35px 0 15px;">逾期借阅记录</h3>
      <table>
        <thead>
          <tr>
            <th>借阅ID</th>
            <th>用户名</th>
            <th>图书标题</th>
            <th>借阅日期</th>
            <th>应还日期</th>
            <th>逾期天数</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in overdueBorrow" :key="item.borrow_id">
            <td>{{ item.borrow_id }}</td>
            <td>{{ item.username }}</td>
            <td>{{ item.book_title }}</td>
            <td>{{ formatDate(item.borrow_date) }}</td>
            <td>{{ formatDate(item.due_date) }}</td>
            <td>{{ item.days_overdue }}</td>
          </tr>
        </tbody>
      </table>

      <!-- 借阅详情查询 -->
      <h3 style="color: #2c3e50; margin: 35px 0 15px;">借阅详情</h3>
      <div class="search-section">
        <input 
          v-model="searchUserId" 
          type="number" 
          placeholder="输入用户ID查询（可选）" 
          class="search-input"
        />
        <button @click="fetchBorrowDetail" class="action-button">查询借阅详情</button>
      </div>
      <table>
        <thead>
          <tr>
            <th>借阅ID</th>
            <th>用户名</th>
            <th>图书标题</th>
            <th>作者</th>
            <th>分类</th>
            <th>借阅日期</th>
            <th>应还日期</th>
            <th>状态</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in borrowDetail" :key="item.borrow_id">
            <td>{{ item.borrow_id }}</td>
            <td>{{ item.username }}</td>
            <td>{{ item.book_title }}</td>
            <td>{{ item.author_names }}</td>
            <td>{{ item.category_name }}</td>
            <td>{{ formatDate(item.borrow_date) }}</td>
            <td>{{ formatDate(item.due_date) }}</td>
            <td>
              <span :class="getStatusClass(item.status)">{{ item.status }}</span>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- 借阅过某图书的用户 -->
      <h3 style="color: #2c3e50; margin: 35px 0 15px;">借阅过某图书的用户</h3>
      <div class="search-section">
        <input 
          v-model="searchBookIdForUsers" 
          type="number" 
          placeholder="输入图书ID" 
          class="search-input"
        />
        <button @click="fetchUsersBorrowedBook" class="action-button">查询用户</button>
      </div>
      <table>
        <thead>
          <tr>
            <th>用户ID</th>
            <th>用户名</th>
            <th>借阅日期</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in usersBorrowedBook" :key="`${item.user_id}-${item.borrow_date}`">
            <td>{{ item.user_id }}</td>
            <td>{{ item.username }}</td>
            <td>{{ formatDate(item.borrow_date) }}</td>
          </tr>
        </tbody>
      </table>

      <!-- 活跃用户或管理员查询 -->
      <h3 style="color: #2c3e50; margin: 35px 0 15px;">活跃用户或管理员</h3>
      <table>
        <thead>
          <tr>
            <th>用户ID</th>
            <th>用户名</th>
            <th>角色</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in activeOrAdminUsers" :key="item.user_id">
            <td>{{ item.user_id }}</td>
            <td>{{ item.username }}</td>
            <td>{{ item.role }}</td>
          </tr>
        </tbody>
      </table>

      <!-- 借阅过所有分类的用户 -->
      <h3 style="color: #2c3e50; margin: 35px 0 15px;">借阅过所有分类的用户</h3>
      <table>
        <thead>
          <tr>
            <th>用户ID</th>
            <th>用户名</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in usersBorrowedAllCategories" :key="item.user_id">
            <td>{{ item.user_id }}</td>
            <td>{{ item.username }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- 权限提示 -->
    <div v-else class="permission-info">
      <p>权限不足：统计查询功能仅限管理员使用。</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useUserStore } from '../../store/user.js';
import { 
  getBookCountByCategory, 
  getMostActiveUsers,
  getBookAuthors,
  getCategoryTree,
  getOverdueBorrow,
  getBorrowDetail,
  getUsersBorrowedBook,
  getActiveOrAdminUsers,
  getUsersBorrowedAllCategories
} from '../../api/query.js';

// 响应式数据
const bookCountByCategory = ref([]);
const mostActiveUsers = ref([]);
const bookAuthors = ref([]);
const categoryTree = ref([]);
const overdueBorrow = ref([]);
const borrowDetail = ref([]);
const usersBorrowedBook = ref([]);
const activeOrAdminUsers = ref([]);
const usersBorrowedAllCategories = ref([]);

// 搜索参数
const searchBookId = ref('');
const searchUserId = ref('');
const searchBookIdForUsers = ref('');

const userStore = useUserStore();

// 通过getter判断是否为管理员
const isAdmin = computed(() => userStore.isAdmin);

// 格式化日期
const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('zh-CN');
};

// 获取状态样式类
const getStatusClass = (status) => {
  if (status === 'overdue') return 'status-overdue';
  if (status === 'returned') return 'status-returned';
  return 'status-borrowed';
};

// 获取所有统计数据
const fetchStats = async () => {
  if (!isAdmin.value) {
    console.log('普通用户无法访问统计查询功能');
    return;
  }
  
  try {
    // 获取基础统计信息
    const [res1, res2, res3, res4, res5, res6] = await Promise.allSettled([
      getBookCountByCategory(),
      getMostActiveUsers(),
      getCategoryTree(),
      getOverdueBorrow(),
      getActiveOrAdminUsers(),
      getUsersBorrowedAllCategories()
    ]);

    if(res1.status === 'fulfilled' && res1.value.code === 0) bookCountByCategory.value = res1.value.data;
    if(res2.status === 'fulfilled' && res2.value.code === 0) mostActiveUsers.value = res2.value.data;
    if(res3.status === 'fulfilled' && res3.value.code === 0) categoryTree.value = res3.value.data;
    if(res4.status === 'fulfilled' && res4.value.code === 0) overdueBorrow.value = res4.value.data;
    if(res5.status === 'fulfilled' && res5.value.code === 0) activeOrAdminUsers.value = res5.value.data;
    if(res6.status === 'fulfilled' && res6.value.code === 0) usersBorrowedAllCategories.value = res6.value.data;

    // 获取借阅详情（不指定用户ID）
    const detailRes = await getBorrowDetail();
    if(detailRes.code === 0) borrowDetail.value = detailRes.data;
  } catch (error) {
    console.error('获取统计数据失败:', error);
  }
};

// 查询图书作者
const fetchBookAuthors = async () => {
  if (!isAdmin.value) return;
  
  try {
    const params = {};
    if (searchBookId.value) {
      params.book_id = searchBookId.value;
    }
    
    const res = await getBookAuthors(params);
    if(res.code === 0) {
      bookAuthors.value = res.data;
    }
  } catch (error) {
    console.error('获取图书作者失败:', error);
  }
};

// 查询借阅详情
const fetchBorrowDetail = async () => {
  if (!isAdmin.value) return;
  
  try {
    const params = {};
    if (searchUserId.value) {
      params.user_id = searchUserId.value;
    }
    
    const res = await getBorrowDetail(params);
    if(res.code === 0) {
      borrowDetail.value = res.data;
    }
  } catch (error) {
    console.error('获取借阅详情失败:', error);
  }
};

// 查询借阅过某图书的用户
const fetchUsersBorrowedBook = async () => {
  if (!isAdmin.value || !searchBookIdForUsers.value) return;
  
  try {
    const res = await getUsersBorrowedBook(searchBookIdForUsers.value);
    if(res.code === 0) {
      usersBorrowedBook.value = res.data;
    }
  } catch (error) {
    console.error('获取借阅过某图书的用户失败:', error);
  }
};

onMounted(fetchStats);
</script>

<style scoped>
/* 通用容器样式 */
.list-container {
  max-width: 1200px;
  margin: 20px auto;
  padding: 25px;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
}

.list-container h2 {
  color: #2c3e50;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid #f0f4f8;
  font-weight: 600;
}

.list-container h3 {
  color: #2c3e50;
  margin: 25px 0 15px;
  font-weight: 500;
}

/* 搜索区域样式 */
.search-section {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
  align-items: center;
}

.search-input {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
  width: 250px;
}

/* 按钮样式 */
.action-button {
  padding: 10px 20px;
  background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.action-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(52, 152, 219, 0.3);
}

/* 表格样式 */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  border-radius: 8px;
  overflow: hidden;
}

th, td {
  padding: 15px;
  text-align: left;
  border-bottom: 1px solid #e0e6ed;
}

th {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  font-weight: 600;
  color: #2c3e50;
  text-transform: uppercase;
  font-size: 14px;
  letter-spacing: 0.5px;
}

tr:nth-child(even) {
  background-color: #f8fafc;
}

tr:hover {
  background-color: #f1f8ff;
  transition: background-color 0.2s;
}

/* 状态样式 */
.status-overdue {
  color: #e74c3c;
  font-weight: bold;
}

.status-returned {
  color: #27ae60;
}

.status-borrowed {
  color: #f39c12;
}

.permission-info {
  margin-top: 20px;
  padding: 15px;
  background-color: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 4px;
  color: #856404;
}
</style>