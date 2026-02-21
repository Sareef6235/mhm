const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');
const sanitize = require('./middleware/sanitize');

const app = express();

app.use(helmet());
app.use(cors({ origin: process.env.CLIENT_ORIGIN?.split(',') || '*' }));
app.use(morgan('dev'));
app.use(express.json({ limit: '2mb' }));
app.use(sanitize);

app.get('/health', (_req, res) => res.json({ ok: true }));
app.use('/api/auth', require('./routes/authRoutes'));
app.use('/api/exams', require('./routes/examRoutes'));
app.use('/api/results', require('./routes/resultRoutes'));

app.use((err, _req, res, _next) => {
  console.error(err);
  return res.status(500).json({ message: 'Unexpected server error' });
});

module.exports = app;
