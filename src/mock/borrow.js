let mockBorrows = [
  { borrow_id: 1, user_id: 2, username: 'alice', book_id: 1, book_title: 'Database System', borrow_date: '2024-12-01', due_date: '2025-01-01', return_date: null, status: 'borrowed' }
];

export function listUserBorrows(user_id) {
  return new Promise(resolve => setTimeout(() => {
    resolve({ code: 0, message: 'success', data: mockBorrows.filter(b => b.user_id === user_id) });
  }, 200));
}

export function createBorrow(data) {
  return new Promise(resolve => {
    const newBorrow = { borrow_id: mockBorrows.length + 1, ...data, status: 'borrowed' };
    mockBorrows.push(newBorrow);
    setTimeout(() => resolve({ code: 0, message: 'borrow record created successfully', data: newBorrow }), 200);
  });
}

export function returnBook(borrow_id) {
  return new Promise(resolve => {
    const borrow = mockBorrows.find(b => b.borrow_id === borrow_id);
    if(borrow){
      borrow.return_date = new Date().toISOString().split('T')[0];
      borrow.status = 'returned';
    }
    setTimeout(() => resolve({ code: 0, message: 'book returned successfully', data: null }), 200);
  });
}
