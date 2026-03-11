# Madrasa Book Ordering Web Application

A complete PHP + MySQL web app for student book ordering and admin order/book management, ready for cPanel deployment.

## Default Admin Login
- Username: `admin`
- Password: `mhnu1234`

## Key Update: Class-Based Dropdown (1-12)
- Student booking page now shows Class 1 to Class 12 as Bootstrap accordion dropdown sections.
- Clicking one class expands only that class section and collapses others.
- Inside each class section, books are loaded dynamically from MySQL and shown in a table with Book Name, Price, Quantity, and Book button.

## Project Structure
```
/public_html
  index.php
  order.php
  myorders.php
/admin
  login.php
  dashboard.php
  books.php
  orders.php
  export_orders.php
  logout.php
/config
  db.php
/assets
  style.css
  script.js
/database
  madrasa_books.sql
```

## Setup (Local or cPanel)
1. Create MySQL database and user in cPanel.
2. Import `database/madrasa_books.sql` in phpMyAdmin.
3. Update DB credentials in `config/db.php`.
4. Upload all project files under `public_html` (keep folder structure).
5. Visit `/public_html/index.php` and `/admin/login.php`.

## Security Measures Included
- Prepared statements for DB queries.
- Password hashing with `password_hash` / `password_verify`.
- CSRF token checks on forms.
- Session-based admin authentication.
- Output escaping via `htmlspecialchars`.
