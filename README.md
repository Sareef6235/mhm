# Madrasa Book Ordering Web Application (PHP + MySQL)

## Latest Fixes + UI Upgrade
- Repaired critical pages that caused 500 errors (`order.php`, `admin/orders.php`, `admin/dashboard.php`, `config/db.php`).
- Added safe DB bootstrap with MySQL-only PDO connection (no SQLite fallback).
- Kept helper functions available globally: `e()`, `csrf_token()`, `verify_csrf()`, `require_admin()`.
- Added modern global footer on all pages:
  - Center text: **Design by Muhsin Faizy**
  - Soft glow highlight
  - Hover color change and clickable portfolio link

## Student Flow
1. Open `public_html/index.php`
2. Enter Student Name + Class
3. Go to `order.php` and place textbook/notebook order

## Admin Flow
- Login: `admin/login.php`
- Credentials: `admin / mhnu1234`
- Dashboard analytics + order filters + CSV export

## cPanel Setup
1. Upload files preserving folders.
2. Create MySQL DB and user.
3. Import `database/madrasa_books.sql`.
4. Set DB credentials in `config/db.php`.
5. Open `public_html/index.php`.
