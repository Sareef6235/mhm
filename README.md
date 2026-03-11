# Modern Madrasa Book Ordering Web Application (PHP + MySQL)

## Core Update (No Student Login)
- Students now open the site and directly access the order form (`public_html/order.php`).
- Student details are filled inside the order form itself.
- Unique Class Number logic is still enforced: `Class + Gender + Class Number` must be unique.

## Features
- Direct student order form fields:
  - Student Name
  - Class (1-12)
  - Gender (Male/Female)
  - Class Number
  - Phone
- Class-based textbook visibility (only selected class books are shown).
- Separate sections for Textbooks and Notebooks.
- Live total amount calculation using JavaScript.
- Order summary before confirmation.
- Admin dashboard with analytics cards + Chart.js charts.
- Admin management for Textbooks and Notebooks (Add/Edit/Delete).
- Admin filters by Class, Gender, Book Name, Date + CSV export.

## Default Admin
- Username: `admin`
- Password: `mhnu1234`

## Main Pages
- `public_html/order.php` (direct student ordering page)
- `public_html/myorders.php` (order tracking)
- `admin/login.php`
- `admin/dashboard.php`
- `admin/textbooks.php`
- `admin/notebooks.php`
- `admin/orders.php`
- `admin/export_orders.php`

## cPanel Deployment
1. Upload files in File Manager (preserve folders).
2. Create MySQL DB/user.
3. Import `database/madrasa_books.sql` via phpMyAdmin.
4. Update `config/db.php` credentials if needed.
5. Open `public_html/order.php` for students and `admin/login.php` for admin.
