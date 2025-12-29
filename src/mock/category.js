let mockCategories = [
  { category_id: 1, category_name: 'Computer Science', description: 'CS books', parent_id: null },
  { category_id: 2, category_name: 'Mathematics', description: 'Math books', parent_id: null }
];

export function listCategories() {
  return new Promise(resolve => setTimeout(() => resolve({ code: 0, message: 'success', data: mockCategories }), 200));
}

export function createCategory(data) {
  return new Promise(resolve => {
    const newCat = { category_id: mockCategories.length + 1, ...data };
    mockCategories.push(newCat);
    setTimeout(() => resolve({ code: 0, message: 'category created successfully', data: newCat }), 200);
  });
}
