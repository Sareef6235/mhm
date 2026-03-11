# Madrasa Book Ordering Web Application (PHP + MySQL)

## ✅ Fixed & Upgraded
- Repaired core pages that previously caused HTTP 500 (`order.php`, `admin/orders.php`, `admin/dashboard.php`, `config/db.php`).
- Added robust PDO setup, helper functions (`e`, `csrf_token`, `verify_csrf`), and automatic table checks to avoid crash on missing tables.
- Enabled development error display for faster debugging.

## Student Flow (No Traditional Login)
1. Students open `public_html/index.php`.
2. Fill Student Entry Form:
   - Student Name
   - Class (1-12)
3. They are redirected to order page for that class.

## Order Page Features
- Shows only textbooks of selected class.
- Gender + Class Number fields for unique numbering logic.
- Rule: `Class + Gender + Class Number` must be unique.
- Two order sections:
  - Text Books (`textbooks` table)
  - Note Books (`notebooks` table)
- Live total auto-calculation with JavaScript.
- Order review summary before confirm.

## Admin Panel
- Admin Login: `admin / mhnu1234`
- Dashboard cards:
  - Total Students
  - Total Orders
  - Total Books Ordered
  - Total Notebooks Ordered
  - Total Amount
- Analytics:
  - Most ordered books
  - Orders per class
  - Male/Female order split
- Orders page supports filters:
  - Class
  - Gender
  - Book Name
  - Date
- CSV export available.

## cPanel Deployment
1. Upload project into `public_html` (keep folders).
2. Create MySQL DB/user.
3. Import `database/madrasa_books.sql`.
4. Update DB credentials in `config/db.php`.
5. Open student entry: `public_html/index.php`.

