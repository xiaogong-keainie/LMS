import { mockBooks } from './book.js';
import { mockUsers } from './user.js';

export function bookCountByCategory() {
  return new Promise(resolve => {
    const data = [
      { category_name: 'Computer Science', book_count: mockBooks.filter(b => b.category_name === 'Computer Science').length },
      { category_name: 'Mathematics', book_count: mockBooks.filter(b => b.category_name === 'Mathematics').length }
    ];
    setTimeout(() => resolve({ code: 0, message: 'success', data }), 200);
  });
}

export function mostActiveUsers() {
  return new Promise(resolve => {
    const data = mockUsers.map(u => ({ user_id: u.user_id, username: u.username, borrow_count: Math.floor(Math.random()*10)+1 }));
    setTimeout(() => resolve({ code: 0, message: 'success', data }), 200);
  });
}
