import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import rateLimit from 'express-rate-limit';
import authRoutes from './routes/authRoutes.js';
import resultRoutes from './routes/resultRoutes.js';
import settingsRoutes from './routes/settingsRoutes.js';
import importRoutes from './routes/importRoutes.js';
import certificateRoutes from './routes/certificateRoutes.js';
import { errorHandler, notFoundHandler } from './middleware/error.js';
import { env } from './config/env.js';

const app = express();

app.use(helmet());
app.use(cors({ origin: env.clientUrl }));
app.use(express.json({ limit: '5mb' }));
app.use(rateLimit({ windowMs: 15 * 60 * 1000, max: 300 }));

app.get('/api/health', (_, res) => res.json({ status: 'ok' }));
app.use('/api/auth', authRoutes);
app.use('/api/results', resultRoutes);
app.use('/api/settings', settingsRoutes);
app.use('/api/import', importRoutes);
app.use('/api/certificate', certificateRoutes);

app.use(notFoundHandler);
app.use(errorHandler);

export default app;
