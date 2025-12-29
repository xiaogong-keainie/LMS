// src/store/user.js
import { defineStore } from 'pinia';

export const useUserStore = defineStore('user', {
  state: () => ({
    currentUser: null,
  }),
  
  getters: {
    isLoggedIn: (state) => !!state.currentUser,
    isAdmin: (state) => state.currentUser?.role === 'ADMIN',
    isReader: (state) => state.currentUser?.role === 'READER',
    hasRole: (state) => (role) => state.currentUser?.role === role,
  },
  
  actions: {
  login(user) {
    this.currentUser = user;
    localStorage.setItem('currentUser', JSON.stringify(user));
  },
  
  logout() {
    this.currentUser = null;
    localStorage.removeItem('currentUser');
  },
  
  updateRole(role) {
    if (this.currentUser) {
      this.currentUser.role = role;
      // 更新用户信息时也更新 localStorage
      localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
    }
  },
  
  // 从本地存储加载用户信息
  loadUserFromStorage() {
    const storedUser = localStorage.getItem('currentUser');
    if (storedUser) {
      this.currentUser = JSON.parse(storedUser);
    }
  },
  
  // 检查用户是否已登录（从本地存储）
  checkAuth() {
    this.loadUserFromStorage();
    return !!this.currentUser;
  }
},
});