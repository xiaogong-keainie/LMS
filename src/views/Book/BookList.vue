<template>
  <div class="list-container">
    <h2>图书管理</h2>
    <button 
      v-if="isAdmin" 
      class="action-button" 
      @click="openCreateForm"
    >
      新增图书
    </button>
    
    <!-- 创建/编辑图书的模态框 -->
    <div v-if="showForm" class="modal-overlay" @click="closeForm">
      <div class="modal-content" @click.stop>
        <h3>{{ editingBook ? '编辑图书' : '新增图书' }}</h3>
        <form @submit.prevent="submitBook">
          <div class="form-group">
            <label>书名:</label>
            <input v-model="form.title" required />
          </div>
          <div class="form-group">
            <label>ISBN:</label>
            <input v-model="form.isbn" required />
          </div>
          <div class="form-group">
            <label>分类ID:</label>
            <input v-model.number="form.category_id" required />
          </div>
          <div class="form-group">
            <label>出版社ID:</label>
            <input v-model.number="form.publisher_id" required />
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
          <th>书名</th>
          <th>ISBN</th>
          <th>分类</th>
          <th>出版社</th>
          <th>出版日期</th>
          <th>总库存</th>
          <th>可借库存</th>
          <th v-if="isAdmin">操作</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="book in books" :key="book.book_id">
          <td>{{ book.title }}</td>
          <td>{{ book.isbn }}</td>
          <td>{{ book.category_name }}</td>
          <td>{{ book.publisher_name }}</td>
          <td>{{ book.publish_date }}</td>
          <td>{{ book.total_stock }}</td>
          <td>{{ book.available_stock }}</td>
          <td v-if="isAdmin">
            <button class="operation-button edit-button" @click="editBook(book)">编辑</button>
            <button class="operation-button delete-button" @click="deleteBookById(book.book_id)">删除</button>
          </td>
        </tr>
      </tbody>
    </table>
    
    <!-- 分页信息 -->
    <div v-if="pagination" class="pagination">
      <p>共 {{ pagination.total }} 条记录，当前第 {{ pagination.page }} 页，共 {{ pagination.pages }} 页</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { listBooks, createBook, updateBook, deleteBook } from '../../api/book.js';
import { useUserStore } from '../../store/user.js';

const books = ref([]);
const userStore = useUserStore();
const showForm = ref(false);
const editingBook = ref(null);
const form = ref({
  title: '',
  isbn: '',
  category_id: '',
  publisher_id: ''
});

// 添加分页信息
const pagination = ref(null);

// 通过getter判断是否为管理员
const isAdmin = computed(() => userStore.isAdmin);

const fetchBooks = async () => {
  try {
    const res = await listBooks();
    if(res.code === 0) {
      // 根据实际返回的数据结构，图书数据在 res.data.books 中
      books.value = res.data?.books || [];
      // 获取分页信息
      pagination.value = res.data?.pagination || null;
      console.log('获取图书列表成功:', res.data); // 添加调试信息
    } else {
      console.error('获取图书列表失败:', res.message);
    }
  } catch (error) {
    console.error('获取图书列表错误:', error);
  }
};

const openCreateForm = () => {
  editingBook.value = null;
  form.value = {
    title: '',
    isbn: '',
    category_id: '',
    publisher_id: ''
  };
  showForm.value = true;
};

const editBook = (book) => {
  editingBook.value = book;
  form.value = {
    title: book.title,
    isbn: book.isbn,
    category_id: book.category_id,
    publisher_id: book.publisher_id
  };
  showForm.value = true;
};

const closeForm = () => {
  showForm.value = false;
  editingBook.value = null;
};

const submitBook = async () => {
  try {
    if (editingBook.value) {
      // 更新图书
      const response = await updateBook(editingBook.value.book_id, form.value);
      if (response.code === 0) {
        alert('图书更新成功');
        closeForm();
        fetchBooks(); // 重新获取图书列表
      } else {
        alert('更新失败: ' + response.message);
      }
    } else {
      // 创建图书
      const response = await createBook(form.value);
      if (response.code === 0) {
        alert('图书创建成功');
        closeForm();
        fetchBooks(); // 重新获取图书列表
      } else {
        alert('创建失败: ' + response.message);
      }
    }
  } catch (error) {
    console.error('提交图书错误:', error);
    alert('操作失败，请检查网络连接');
  }
};

const deleteBookById = async (id) => {
  if (confirm('确认删除该图书？此操作不可撤销')) {
    try {
      const response = await deleteBook(id);
      if (response.code === 0) {
        alert('删除成功');
        fetchBooks(); // 重新获取图书列表
      } else {
        alert('删除失败: ' + response.message);
      }
    } catch (error) {
      console.error('删除图书错误:', error);
      alert('删除失败，请检查网络连接');
    }
  }
};

onMounted(fetchBooks);
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

.operation-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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

/* 分页信息样式 */
.pagination {
  margin-top: 20px;
  text-align: center;
  color: #666;
}
</style>