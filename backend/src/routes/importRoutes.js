import { Router } from 'express';
import { previewHeaders, previewSheets, transform } from '../controllers/importController.js';
import { authRequired } from '../middleware/auth.js';

const router = Router();

router.post('/sheets', authRequired, previewSheets);
router.post('/headers', authRequired, previewHeaders);
router.post('/transform', authRequired, transform);

export default router;
