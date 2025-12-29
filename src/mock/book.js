let mockBooks = [
  { book_id: 1, title: 'Database System', isbn: '978-0123456789', category_name: 'Computer Science', publisher_name: 'Academic Press', total_stock: 10, available_stock: 8 },
  { book_id: 2, title: 'Math Basics', isbn: '978-9876543210', category_name: 'Mathematics', publisher_name: 'Math Press', total_stock: 5, available_stock: 5 }
];

export function listBooks() {
  return new Promise(resolve => setTimeout(() => resolve({ code: 0, message: 'success', data: mockBooks }), 200));
}

export function createBook(data) {
  return new Promise(resolve => {
    const newBook = { book_id: mockBooks.length + 1, ...data };
    mockBooks.push(newBook);
    setTimeout(() => resolve({ code: 0, message: 'book created successfully', data: newBook }), 200);
  });
}

export function updateBook(bookId, data) {
  return new Promise(resolve => {
    const index = mockBooks.findIndex(b => b.book_id === bookId);
    if(index !== -1) mockBooks[index] = { ...mockBooks[index], ...data };
    setTimeout(() => resolve({ code: 0, message: 'book updated successfully', data: null }), 200);
  });
}

export function deleteBook(bookId) {
  return new Promise(resolve => {
    mockBooks = mockBooks.filter(b => b.book_id !== bookId);
    setTimeout(() => resolve({ code: 0, message: 'book deleted successfully', data: null }), 200);
  });
}
