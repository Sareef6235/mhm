# Modern Madrasa Book Ordering Web Application (PHP + MySQL)

## Features
- Student login with: Name, Class (1-12), Gender, Class Number, Phone.
- Unique validation for `Class + Gender + Class Number`.
- Students can view/order only textbooks of their class.
- Separate ordering sections for Textbooks and Notebooks.
- Live JavaScript total amount calculation.
- Order summary before confirm.
- Admin dashboard with analytics cards + Chart.js charts.
- Admin management for Textbooks and Notebooks (Add/Edit/Delete).
- Admin order filtering by Class, Gender, Book Name, Date and CSV export.

## Default Admin
- Username: `admin`
- Password: `mhnu1234`

## File Structure
- `public_html/login.php` (student login)
- `public_html/order.php` (class-restricted textbook + notebook ordering)
- `public_html/myorders.php` (student order history)
- `admin/login.php`
- `admin/dashboard.php`
- `admin/textbooks.php`
- `admin/notebooks.php`
- `admin/orders.php`
- `admin/export_orders.php`
- `config/db.php`
- `assets/style.css`, `assets/script.js`
- `database/madrasa_books.sql`

## cPanel Deployment
1. Upload files to `public_html` preserving folders.
2. Create MySQL DB/user in cPanel.
3. Import `database/madrasa_books.sql` via phpMyAdmin.
4. Update DB credentials in `config/db.php` if needed.
5. Open `public_html/login.php` for students and `admin/login.php` for admin.
