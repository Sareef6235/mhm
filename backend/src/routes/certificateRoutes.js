import { Router } from 'express';
import { certificateData } from '../controllers/certificateController.js';

const router = Router();
router.get('/:shortCode', certificateData);

export default router;
