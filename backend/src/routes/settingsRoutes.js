import { Router } from 'express';
import { authRequired } from '../middleware/auth.js';
import { getCertificateConfig, getSettings, upsertCertificateConfig, upsertSettings } from '../controllers/settingsController.js';

const router = Router();

router.get('/exam', authRequired, getSettings);
router.post('/exam', authRequired, upsertSettings);
router.get('/certificate', authRequired, getCertificateConfig);
router.post('/certificate', authRequired, upsertCertificateConfig);

export default router;
