# Exam Result Management Web Application

Production-ready full-stack application with Next.js frontend and Express + MySQL backend for exam result publishing, certificate verification, and admin operations.

## Folder Structure

- `frontend/` - Next.js 14 app with Tailwind glass UI, Framer Motion, i18next multi-language, and admin/public modules.
- `backend/` - Express API with Sequelize ORM, JWT auth, rate limiting, validation, sanitization, and MySQL schema.

## Features Implemented

- Public result search (register number + optional DOB) with QR verification payload.
- Short-link system (`/r/:shortcode`) backed by `result_links` table.
- JWT-protected admin APIs for exam setup, JSON upload, certificate template, and link management.
- Auto-calculation of total, percentage, grade, and pass/fail before student inserts.
- Google Sheet mapping preview endpoint for converting tabular rows to structured JSON.
- Responsive modern UI with glassmorphism, gradient background, and admin sidebar layout.
- i18n setup for English, Malayalam, Hindi, Tamil, Arabic.
- cPanel-ready `.env` configs and SQL migration script.

## Quick Start

### Backend

1. `cd backend`
2. `cp .env.example .env`
3. Update MySQL values (`DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`, `DB_PORT`).
4. Import `migrations/schema.sql` in phpMyAdmin.
5. `npm install`
6. `npm run dev`

### Frontend

1. `cd frontend`
2. `cp .env.example .env.local`
3. `npm install`
4. `npm run dev`

## cPanel Deployment Notes

1. Upload `backend` and `frontend` to your hosting account.
2. Create database/users via cPanel and update backend `.env` variables.
3. Import `backend/migrations/schema.sql` via phpMyAdmin.
4. Build frontend with `npm run build` and run using Node.js app manager.
5. Set reverse proxy for API domain/subdomain to backend `server.js` process.
6. Ensure `CLIENT_ORIGIN` and `NEXT_PUBLIC_API_URL` point to production domains.

## Security & Performance

- JWT auth + bcrypt password hashing for admins.
- Input validation with Joi and sanitization against XSS payloads.
- Search endpoint rate limiting.
- Sequelize parameterized queries (SQL injection safe by default).
- Memory cache for repeated search requests.
- Indexed student columns for fast lookups.

