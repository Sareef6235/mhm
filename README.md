# Exam Result Management Web Application

Production-ready full-stack app with Next.js + Tailwind frontend and Express + MySQL backend.

## Features
- Public result search by register number and optional DOB.
- PDF download for result + certificate PDF with QR verification.
- JWT-protected admin panel.
- Google Sheets import, transform to normalized JSON, and bulk insert.
- Dynamic exam settings (pass mark, total marks, grade slabs).
- Result short links (`/r/:shortCode`) and certificate verification.
- Multi-language UI (English, Malayalam, Hindi, Tamil, Arabic).
- Responsive glassmorphism design with animation.

## Folder Structure
- `backend/`: Express API, MySQL integration, JWT auth.
- `frontend/`: Next.js application with public pages and admin panel.
- `backend/sql/schema.sql`: MySQL schema + admin seed.

## Backend Setup
```bash
cd backend
cp .env.example .env
npm install
npm run dev
```

## Frontend Setup
```bash
cd frontend
cp .env.example .env.local
npm install
npm run dev
```

## Environment Variables
Backend (`backend/.env`):
- `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`
- `JWT_SECRET`, `JWT_EXPIRES_IN`
- `GOOGLE_SHEETS_API_KEY`
- `CLIENT_URL`

Frontend (`frontend/.env.local`):
- `NEXT_PUBLIC_API_URL`

## Deployment
- Backend deployable on VPS/Railway/cPanel Node app.
- Frontend deployable on Vercel or static-compatible Node host.
- MySQL schema is phpMyAdmin compatible.

## Security Practices Included
- Helmet, CORS, rate limiting.
- JWT middleware on all admin routes.
- Joi schema validation for result payloads.
- Input sanitization before DB insert.

## Default Admin
- Username: `admin`
- Password: `admin123`
> Change immediately after first login.
