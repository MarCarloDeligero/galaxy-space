User Management System - Setup Instructions

1. Copy this folder to your web server root (e.g., htdocs for XAMPP).
2. Edit includes/config.php and set your DB credentials.
3. Import sql/setup.sql into your MySQL server (phpMyAdmin or mysql client).
   Example: mysql -u root -p < sql/setup.sql
4. Run create_admin.php once in your browser to create an admin account, then delete that file.
   e.g., http://localhost/user_management_complete/create_admin.php
5. Visit index.php to use the app. Admin panel at admin/index.php.
6. This is a learning/demo project. Do NOT use as-is in production. Add CSRF, HTTPS, input sanitization, and other protections before deploying.
