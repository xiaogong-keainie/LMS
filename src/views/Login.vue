<template>
  <div class="login-container">
    <h2>登录</h2>
    <form @submit.prevent="handleLogin">
      <input v-model="username" placeholder="用户名" required/>
      <input v-model="password" type="password" placeholder="密码" required/>
      <button type="submit">登录</button>
    </form>
  </div>
  <p>还没有账号？<router-link to="/register">注册</router-link></p>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../store/user.js';
import { loginUser } from '../api/user.js';

const username = ref('');
const password = ref('');
const router = useRouter();
const userStore = useUserStore();

const handleLogin = async () => {
  try {
    const res = await loginUser({ username: username.value, password: password.value });
    if(res.code === 0){
      userStore.login(res.data);
      router.push('/dashboard/books');
    } else {
      alert(res.message);
    }
  } catch(err) {
    console.error(err);
    alert('登录失败');
  }
}

</script>

<style scoped>
.login-container { 
  max-width: 400px; 
  margin: 100px auto; 
  padding: 30px;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  backdrop-filter: blur(10px);
}

.login-container h2 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 25px;
  font-weight: 600;
}

input { 
  display: block; 
  width: 100%; 
  margin: 15px 0; 
  padding: 12px; 
  border: 2px solid #e0e6ed;
  border-radius: 8px;
  transition: border-color 0.3s;
  font-size: 16px;
}

input:focus { 
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}

button { 
  padding: 12px 20px; 
  width: 100%;
  background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  transition: transform 0.2s, box-shadow 0.2s;
}

button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(52, 152, 219, 0.3);
}

p { 
  margin-top: 20px; 
  text-align: center;
  color: #7f8c8d;
}

p a {
  color: #3498db;
  text-decoration: none;
  font-weight: 500;
}

p a:hover {
  text-decoration: underline;
}
</style>