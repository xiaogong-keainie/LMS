let mockAuthors = [
  { author_id: 1, name: 'Author A', country: 'Country A' },
  { author_id: 2, name: 'Author B', country: 'Country B' }
];

export function listAuthors() {
  return new Promise(resolve => setTimeout(() => resolve({ code: 0, message: 'success', data: mockAuthors }), 200));
}

export function createAuthor(data) {
  return new Promise(resolve => {
    const newAuthor = { author_id: mockAuthors.length + 1, ...data };
    mockAuthors.push(newAuthor);
    setTimeout(() => resolve({ code: 0, message: 'author created successfully', data: newAuthor }), 200);
  });
}
