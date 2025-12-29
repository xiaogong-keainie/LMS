import request from './index.js';

export function listBorrows(userId) {
  return request.get(`/borrow/user/${userId}`);
}
export function createBorrow(data) {
  return request.post('/borrow/create', data);
}
export function returnBorrow(borrowId) {
  return request.put(`/borrow/return/${borrowId}`);
}
