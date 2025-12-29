<template>
  <div class="register-container">
    <h2>注册</h2>
    <form @submit.prevent="handleRegister">
      <input v-model="username" placeholder="用户名" required />
      <input v-model="email" type="email" placeholder="邮箱" required />
      <input v-model="password" type="password" placeholder="密码" required />
      <button type="submit">注册</button>
    </form>
    <p>
      已有账号？<router-link to="/login">去登录</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { registerUser } from '../api/user.js';

const username = ref('');
const email = ref('');
const password = ref('');
const router = useRouter();

const handleRegister = async () => {
  try {
    const res = await registerUser({ username: username.value, email: email.value, password: password.value });
    if(res.code === 0){
      alert('注册成功，请登录');
      router.push('/login');
    } else {
      alert(res.message);
    }
  } catch(err) {
    console.error(err);
    alert('注册失败');
  }
};
</script>

<style scoped>
.register-container { 
  max-width: 400px; 
  margin: 100px auto; 
  padding: 30px;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  backdrop-filter: blur(10px);
}

.register-container h2 {
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
  border-color: #2ecc71;
  box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2);
}

button { 
  padding: 12px 20px; 
  width: 100%;
  background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
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
  box-shadow: 0 6px 12px rgba(46, 204, 113, 0.3);
}

p { 
  margin-top: 20px; 
  text-align: center;
  color: #7f8c8d;
}

p a {
  color: #2ecc71;
  text-decoration: none;
  font-weight: 500;
}

p a:hover {
  text-decoration: underline;
}
</style>