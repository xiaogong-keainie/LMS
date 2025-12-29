// src/api/query.js
import request from './index.js';

export function getBookAuthors(params) {
  return request.get('/query/book-authors', { params });
}

export function getBookCountByCategory() {
  return request.get('/query/book-count-by-category');
}

export function getMostActiveUsers() {
  return request.get('/query/most-active-users');
}

export function getCategoryTree() {
  return request.get('/query/category-tree');
}

export function getOverdueBorrow() {
  return request.get('/query/overdue-borrow');
}

export function getBorrowDetail(params) {
  return request.get('/query/borrow-detail', { params });
}

export function getUsersBorrowedBook(bookId) {
  return request.get(`/query/users-borrowed-book/${bookId}`);
}

export function getActiveOrAdminUsers() {
  return request.get('/query/active-or-admin-users');
}

export function getUsersBorrowedAllCategories() {
  return request.get('/query/users-borrowed-all-categories');
}