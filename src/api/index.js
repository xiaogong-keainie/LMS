// src/api/index.js
import axios from 'axios';

const request = axios.create({
  baseURL: '/api', // 使用相对路径，通过代理
  timeout: 5000,
  headers: {
    'Content-Type': 'application/json'
  }
});

// 添加请求拦截器
request.interceptors.request.use(
  config => {
    // 可以在这里添加认证token等
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// 添加响应拦截器
request.interceptors.response.use(
  response => {
    return response.data;
  },
  error => {
    console.error('API请求错误:', error);
    return Promise.reject(error);
  }
);

export default request;