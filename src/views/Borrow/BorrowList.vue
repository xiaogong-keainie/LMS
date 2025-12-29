<template>
  <div class="list-container">
    <h2>借阅管理</h2>
    
    <!-- 管理员功能：查询所有借阅记录 -->
    <div v-if="isAdmin" class="admin-controls">
      <div class="search-section">
        <input 
          v-model="searchUserId" 
          placeholder="输入用户ID查询借阅记录" 
          class="search-input"
        />
        <button @click="searchBorrows" class="action-button">查询</button>
        <button @click="resetSearch" class="action-button">重置</button>
      </div>
    </div>
    
    <!-- 普通用户功能：借阅图书 -->
    <div v-if="isReader" class="reader-controls">
      <button class="action-button" @click="showBorrowForm = true">借阅图书</button>
    </div>
    
    <!-- 借阅图书表单 -->
    <div v-if="showBorrowForm" class="modal-overlay" @click="closeBorrowForm">
      <div class="modal-content" @click.stop>
        <h3>借阅图书</h3>
        <form @submit.prevent="submitBorrow">
          <div class="form-group">
            <label>图书ID:</label>
            <input v-model.number="borrowForm.book_id" required />
          </div>
          <div class="form-group">
            <label>借阅天数:</label>
            <input v-model.number="borrowForm.days" type="number" min="1" required />
          </div>
          <div class="form-actions">
            <button type="submit" class="action-button">确认借阅</button>
            <button type="button" @click="closeBorrowForm" class="cancel-button">取消</button>
          </div>
        </form>
      </div>
    </div>
    
    <table>
      <thead>
        <tr>
          <th>用户名</th>
          <th>图书名称</th>
          <th>借出时间</th>
          <th>应还时间</th>
          <th>归还时间</th>
          <th>状态</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="borrow in borrows" :key="borrow.borrow_id">
          <td>{{ borrow.username }}</td>
          <td>{{ borrow.book_title }}</td>
          <td>{{ formatDate(borrow.borrow_date) }}</td>
          <td>{{ formatDate(borrow.due_date) }}</td>
          <td>{{ borrow.return_date ? formatDate(borrow.return_date) : '-' }}</td>
          <td>
            <span :class="statusClass(borrow.status)">{{ statusText(borrow.status) }}</span>
          </td>
          <td>
            <!-- 只有借阅中且是当前用户或管理员才能归还 -->
            <button 
              v-if="borrow.status === 'borrowed' && (isAdmin || borrow.user_id === currentUser.user_id)" 
              @click="returnBook(borrow.borrow_id)"
              class="operation-button return-button"
            >
              归还
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { listBorrows, createBorrow, returnBorrow } from '../../api/borrow.js';
import { useUserStore } from '../../store/user.js';

const borrows = ref([]);
const userStore = useUserStore();
const searchUserId = ref('');
const showBorrowForm = ref(false);
const borrowForm = ref({
  book_id: '',
  days: 30 // 默认30天
});

// 计算属性
const isAdmin = computed(() => userStore.isAdmin);
const isReader = computed(() => userStore.isReader);
const currentUser = computed(() => userStore.currentUser);

// 初始化时根据用户角色获取借阅记录
const fetchBorrows = async (userId = null) => {
  try {
    let response;
    if (isAdmin.value && userId) {
      // 管理员查询特定用户
      response = await listBorrows(userId);
    } else if (isReader.value || (isAdmin.value && !userId)) {
      // 普通用户或管理员查询自己的记录
      response = await listBorrows(currentUser.value.user_id);
    } else {
      // 管理员查询所有记录（如果API支持的话，这里暂时查询当前用户）
      response = await listBorrows(currentUser.value.user_id);
    }
    
    if (response.code === 0) {
      borrows.value = response.data;
    } else {
      console.error('获取借阅记录失败:', response.message);
    }
  } catch (error) {
    console.error('获取借阅记录时发生错误:', error);
  }
};

const searchBorrows = async () => {
  if (!searchUserId.value) {
    alert('请输入用户ID');
    return;
  }
  
  try {
    const response = await listBorrows(searchUserId.value);
    if (response.code === 0) {
      borrows.value = response.data;
    } else {
      console.error('获取借阅记录失败:', response.message);
      alert('查询失败: ' + response.message);
    }
  } catch (error) {
    console.error('查询借阅记录时发生错误:', error);
    alert('查询失败，请稍后重试');
  }
};

const resetSearch = () => {
  searchUserId.value = '';
  fetchBorrows(); // 重新获取当前用户的记录
};

const returnBook = async (borrowId) => {
  if (confirm('确认归还？')) {
    try {
      const response = await returnBorrow(borrowId);
      if (response.code === 0) {
        alert('归还成功');
        fetchBorrows(searchUserId.value || null); // 重新获取数据
      } else {
        alert(`归还失败: ${response.message}`);
      }
    } catch (error) {
      console.error('归还图书时发生错误:', error);
      alert('归还图书失败，请稍后重试');
    }
  }
};

const submitBorrow = async () => {
  try {
    const formData = {
      book_id: borrowForm.value.book_id,
      user_id: currentUser.value.user_id,
      days: borrowForm.value.days
    };
    
    const response = await createBorrow(formData);
    if (response.code === 0) {
      alert('借阅成功');
      closeBorrowForm();
      fetchBorrows(); // 重新获取数据
    } else {
      alert(`借阅失败: ${response.message}`);
    }
  } catch (error) {
    console.error('借阅图书时发生错误:', error);
    alert('借阅失败，请稍后重试');
  }
};

const closeBorrowForm = () => {
  showBorrowForm.value = false;
  borrowForm.value = {
    book_id: '',
    days: 30
  };
};

// 格式化日期显示
const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toISOString().split('T')[0];
};

// 根据状态返回文本
const statusText = (status) => {
  const statusMap = {
    'borrowed': '借阅中',
    'returned': '已归还',
    'overdue': '已逾期'
  };
  return statusMap[status] || status;
};

// 根据状态返回CSS类
const statusClass = (status) => {
  const statusClassMap = {
    'borrowed': 'status-borrowed',
    'returned': 'status-returned',
    'overdue': 'status-overdue'
  };
  return `status-badge ${statusClassMap[status]}`;
};

onMounted(() => {
  fetchBorrows();
});
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

/* 控制面板样式 */
.admin-controls, .reader-controls {
  margin-bottom: 20px;
}

.search-section {
  display: flex;
  gap: 10px;
  align-items: center;
}

.search-input {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  flex: 1;
  max-width: 300px;
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

.cancel-button {
  padding: 10px 20px;
  background: #95a5a6;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  transition: all 0.3s ease;
  margin-left: 10px;
}

.cancel-button:hover {
  background: #7f8c8d;
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

/* 操作按钮样式 */
.operation-button {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  margin-right: 8px;
  transition: all 0.2s ease;
}

.edit-button {
  background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
  color: white;
}

.delete-button {
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
  color: white;
}

.return-button {
  background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
  color: white;
}

.operation-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* 状态徽章样式 */
.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
  text-transform: uppercase;
}

.status-borrowed {
  background-color: #3498db;
  color: white;
}

.status-returned {
  background-color: #2ecc71;
  color: white;
}

.status-overdue {
  background-color: #e74c3c;
  color: white;
}

/* 模态框样式 */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 25px;
  border-radius: 10px;
  width: 500px;
  max-width: 90%;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.modal-content h3 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #2c3e50;
  border-bottom: 2px solid #f0f4f8;
  padding-bottom: 10px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  color: #2c3e50;
}

.form-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  transition: border-color 0.3s;
}

.form-group input:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}
</style>