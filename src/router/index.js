import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Dashboard from '../views/Dashboard.vue';

import BookList from '../views/Book/BookList.vue';
import AuthorList from '../views/Author/AuthorList.vue';
import CategoryList from '../views/Category/CategoryList.vue';
import BorrowList from '../views/Borrow/BorrowList.vue';
import QueryStats from '../views/Query/QueryStats.vue';

import { useUserStore } from '../store/user.js';

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  { path: '/register', component: Register },
  { 
    path: '/dashboard', 
    component: Dashboard,
    children: [
      { path: 'books', component: BookList },
      { path: 'authors', component: AuthorList },
      { path: 'categories', component: CategoryList },
      { path: 'borrows', component: BorrowList },
      { path: 'query', component: QueryStats },
    ]
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// 全局前置守卫
router.beforeEach((to, from, next) => {
  const userStore = useUserStore();
  
  // 每次路由切换都确保用户信息已从本地存储加载
  userStore.loadUserFromStorage();
  
  // 定义公共路由（不需要登录验证的页面）
  const publicRoutes = ['/login', '/register'];
  
  // 如果用户已登录，但访问登录/注册页面，则重定向到仪表板
  if (userStore.isLoggedIn && publicRoutes.includes(to.path)) {
    next('/dashboard/books');
    return;
  }
  
  // 如果用户未登录，且访问的不是公共路由，则重定向到登录页
  if (!userStore.isLoggedIn && !publicRoutes.includes(to.path) && to.path !== '/') {
    next('/login');
    return;
  }
  
  // 如果用户已登录，且访问根路径，则重定向到仪表板
  if (userStore.isLoggedIn && to.path === '/') {
    next('/dashboard/books');
    return;
  }
  
  // 对于根路径'/'，让路由配置的重定向生效
  if (to.path === '/') {
    next(); // 让路由配置的重定向生效
    return;
  }
  
  // 其他情况正常放行
  next();
});

export default router;