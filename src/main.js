import { createApp } from 'vue';
import App from './App.vue';
import './style.css';

import router from './router';
import { createPinia } from 'pinia';
import { useUserStore } from './store/user.js';

const app = createApp(App);

app.use(router);
app.use(createPinia());

// 在应用启动时加载用户状态
const userStore = useUserStore();
userStore.loadUserFromStorage(); // 添加这行
app.mount('#app');
