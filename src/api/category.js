import request from './index.js';

export function listCategories() {
  return request.get('/category/list');
}
export function createCategory(data) {
  return request.post('/category/create', data);
}
