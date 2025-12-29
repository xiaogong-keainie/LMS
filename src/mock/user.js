let mockUsers = [
  { user_id: 1, username: 'admin', role: 'ADMIN', password: '123456' },
  { user_id: 2, username: 'alice', role: 'READER', password: '123456' }
];

export function loginUser({ username, password }) {
  return new Promise(resolve => {
    setTimeout(() => {
      const user = mockUsers.find(u => u.username === username && u.password === password);
      if(user) resolve({ code: 0, message: 'login success', data: user });
      else resolve({ code: 1, message: '用户名或密码错误', data: null });
    }, 200);
  });
}

export function registerUser({ username, email, password }) {
  return new Promise(resolve => {
    mockUsers.push({ user_id: mockUsers.length + 1, username, email, password, role: 'READER' });
    setTimeout(() => resolve({ code: 0, message: 'register success', data: null }), 200);
  });
}
