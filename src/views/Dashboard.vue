<template>
  <div class="dashboard">
    <aside class="sidebar">
      <ul>
        <!-- 所有用户都可以访问的菜单 -->
        <li><router-link to="/dashboard/books">图书浏览</router-link></li>
        
        <!-- 仅管理员可见的菜单 -->
        <li v-if="userStore.isAdmin">
          <router-link to="/dashboard/authors">作者管理</router-link>
        </li>
        <li v-if="userStore.isAdmin">
          <router-link to="/dashboard/categories">分类管理</router-link>
        </li>
        
        <!-- 所有用户都可以查看自己的借阅记录 -->
        <li><router-link to="/dashboard/borrows">借阅管理</router-link></li>
        
        <!-- 仅管理员可见的统计查询 -->
        <li v-if="userStore.isAdmin">
          <router-link to="/dashboard/query">统计查询</router-link>
        </li>
      </ul>
      <button @click="logout">退出登录</button>
    </aside>
    <section class="content">
      <router-view />
    </section>
  </div>
</template>

<script setup>
import { useUserStore } from '../store/user.js';
import { useRouter } from 'vue-router';
import { onMounted } from 'vue';

const userStore = useUserStore();
const router = useRouter();

const logout = () => {
  userStore.logout();
  router.push('/login');
};

// 检查用户权限
onMounted(() => {
  if (!userStore.isLoggedIn) {
    router.push('/login');
  }
});
</script>

<style scoped>
.dashboard { display: flex; height: 100vh; }
.sidebar { width: 200px; background: #2c3e50; color: white; padding: 20px; }
.sidebar ul { list-style: none; padding: 0; }
.sidebar li { margin: 15px 0; }
.sidebar a { color: white; text-decoration: none; }
.content { flex: 1; padding: 20px; overflow-y: auto; }
button { margin-top: 20px; padding: 5px 10px; }
</style>