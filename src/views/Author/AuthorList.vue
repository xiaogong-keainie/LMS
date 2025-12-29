<template>
  <div class="list-container">
    <h2>作者管理</h2>
    
    <!-- 仅管理员显示新增按钮 -->
    <button 
      v-if="isAdmin" 
      class="action-button" 
      @click="openCreateForm"
    >
      新增作者
    </button>

    <!-- 创建作者的模态框 -->
    <div v-if="showForm" class="modal-overlay" @click="closeForm">
      <div class="modal-content" @click.stop>
        <h3>新增作者</h3>
        <form @submit.prevent="submitAuthor">
          <div class="form-group">
            <label>作者姓名:</label>
            <input v-model="form.name" required />
          </div>
          <div class="form-group">
            <label>国籍:</label>
            <input v-model="form.country" required />
          </div>
          <div class="form-actions">
            <button type="submit" class="action-button">保存</button>
            <button type="button" @click="closeForm" class="cancel-button">取消</button>
          </div>
        </form>
      </div>
    </div>
    
    <table>
      <thead>
        <tr>
          <th>作者姓名</th>
          <th>国籍</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="author in authors" :key="author.author_id">
          <td>{{ author.name }}</td>
          <td>{{ author.country }}</td>
        </tr>
      </tbody>
    </table>
    
    <!-- 权限提示 -->
    <div v-if="userStore.isReader" class="permission-info">
      <p>提示：您是普通用户，无法进行作者管理操作。</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useUserStore } from '../../store/user.js';
import { listAuthors, createAuthor } from '../../api/author.js'; // 使用实际API

const authors = ref([]);
const userStore = useUserStore();
const showForm = ref(false);
const form = ref({
  name: '',
  country: ''
});

// 通过getter判断是否为管理员
const isAdmin = computed(() => userStore.isAdmin);

const fetchAuthors = async () => {
  try {
    const res = await listAuthors();
    if(res.code === 0) {
      // 修复：处理包含分页信息的数据结构
      if (res.data && res.data.authors) {
        authors.value = res.data.authors;
      } else {
        // 如果没有分页结构，直接使用 data
        authors.value = res.data || [];
      }
    } else {
      console.error('获取作者列表失败:', res.message);
    }
  } catch (error) {
    console.error('获取作者列表错误:', error);
  }
};

const openCreateForm = () => {
  if (!isAdmin.value) {
    alert('权限不足：只有管理员可以新增作者');
    return;
  }
  
  form.value = {
    name: '',
    country: ''
  };
  showForm.value = true;
};

const closeForm = () => {
  showForm.value = false;
};

const submitAuthor = async () => {
  try {
    // 创建作者
    const response = await createAuthor(form.value);
    if (response.code === 0) {
      alert('作者创建成功');
      closeForm();
      fetchAuthors(); // 重新获取作者列表
    } else {
      alert('创建失败: ' + response.message);
    }
  } catch (error) {
    console.error('提交作者错误:', error);
    alert('操作失败，请检查网络连接');
  }
};

onMounted(fetchAuthors);
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
  margin-bottom: 20px;
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

.permission-info {
  margin-top: 20px;
  padding: 15px;
  background-color: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 4px;
  color: #856404;
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