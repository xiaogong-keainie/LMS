import request from './index.js';

export function listAuthors(params) {
  return request.get('/author/list', { params });
}
export function createAuthor(data) {
  return request.post('/author/create', data);
}
