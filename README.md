# employee-management-system
A lightweight, secure CRUD web application built to manage employee records and organize digital documents (contracts, IDs, and PDFs).

# Features
Full session security (`login.php`) preventing unauthorized direct URL access.
Fast keyword filtering by employee name or department.
Specialized multipart form processing with automatic unique timestamp renaming to prevent file overwriting.
Deleting an employee automatically removes their connected file path logs from the database.
Built entirely on secure PHP PDO prepared statements.

# Technical Stack
Backend: PHP 8.x (PDO Extension).
Database: MySQL / MariaDB.
Frontend: Semantic HTML5 & Clean CSS3.

# 📂 Project Structure

📁 employee_system/
 📁 uploads/           # Document storage folder (git-ignored)
 📄 db.php             # Database connection link
 📄 index.php          # Protected main dashboard & search bar
 📄 add_employee.php   # Form to onboard new workers
 📄 view_employee.php  # Document upload and file history list
 ├── 📄 login.php          # Security gatekeeper form
 ├── 📄 logout.php         # Session clearing script
 └── 📄 .gitignore         # Prevents local user files from being uploaded
