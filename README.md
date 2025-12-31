# Library Management System (LMS)

## Project Overview

The Library Management System (LMS) is a web-based application designed to streamline and automate library operations. This system provides comprehensive management capabilities for books, users, and borrowing records, with distinct roles for regular users and administrators.

### Features

- **User Management**: User registration, login, and role-based access control
- **Book Management**: Comprehensive CRUD operations for book information
- **Author and Category Management**: Maintenance of authors and book categories
- **Borrowing System**: Complete borrowing and return functionality with due dates
- **Query and Analytics**: Advanced search capabilities and statistical reports
- **Role-based Access**: Different permissions for regular users and administrators

### Technology Stack

- **Frontend**: HTML, CSS, JavaScript, Bootstrap
- **Backend**: PHP (or Python Flask)
- **Database**: MySQL
- **Architecture**: B/S (Browser/Server) with front-end and back-end separation

## Deployment and Running Instructions

### Prerequisites

- Web Server (Apache/Nginx)
- PHP 7.4+ (or Python 3.7+ if using Flask)
- MySQL 5.7+
- Node.js and npm (for frontend dependencies)

### Installation Steps

1. **Clone the repository**:

   ```bash
   git clone <repository-url>
   cd LMS
   ```

2. **Set up the database**:
   - Create a MySQL database for the application
   - Import the database schema from the SQL file provided in the `database/` directory

   ```sql
   mysql -u username -p database_name < database/schema.sql
   ```

3. **Configure the database connection**:
   - Update the database configuration in `backend/config.php` (or equivalent config file) with your database credentials

4. **Install frontend dependencies** (if applicable):

   ```bash
   npm install
   ```

5. **Set up the web server**:
   - Configure your web server to point to the project directory
   - Ensure the web server has read/write permissions to necessary directories

6. **Start the application**:
   - For development: Use the built-in PHP server or Python Flask development server
   - For production: Configure with your web server (Apache/Nginx)

### Configuration

- Update `backend/config.php` with your database connection details
- Modify API endpoints in frontend JavaScript files if the backend URL changes
- Adjust any environment-specific settings in configuration files

### Running the Application

1. **Start the database server** (if not already running)
2. **Start the web server**
3. **Access the application** through your web browser at the configured URL

For development purposes, you can use:

```bash
# If using PHP, cd to backend first
php -S localhost:8000

# If using Vite for frontend development
npm run dev
```

The application will be accessible at `http://localhost:8000` (or the configured port).
