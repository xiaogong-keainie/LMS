// src/api/book.js
import request from './index.js';

export function listBooks(params) {
  // 根据LMS文档，接口路径是 /api/book/list
  return request.get('/book/list', { params });
}
export function createBook(data) {
  // 根据LMS文档，接口路径是 /api/book/create
  return request.post('/book/create', data);
}
export function updateBook(bookId, data) {
  // 根据LMS文档，接口路径是 /api/book/update/{book_id}
  return request.put(`/book/update/${bookId}`, data);
}
export function deleteBook(bookId) {
  // 根据LMS文档，接口路径是 /api/book/delete/{book_id}
  return request.delete(`/book/delete/${bookId}`);
}