# Student / Exam / Competition Card Admin System

A cPanel-ready PHP, MySQLi, Bootstrap 5 admin system for black-and-white printable student cards, exam cards, competition cards, chest number cards, PDF print exports, and XLSX exports.

## Install

1. Upload all files to your cPanel public directory.
2. Edit `db.php` with your MySQL host, database, username, and password.
3. Ensure the existing `users` table contains the required student columns.
4. Open `dashboard.php`. Required `card_templates`, `card_settings`, `card_exports`, and `card_logs` tables are created automatically. SQL is also available in `sql/card_system.sql`.

## PDF Export

`export-pdf.php` creates a print-ready A4 black-and-white card page and opens the browser print dialog. Choose **Save as PDF** for a dependency-free cPanel-compatible PDF workflow.
