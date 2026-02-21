import { Router } from 'express';
import {
  bulkInsertResults,
  createResult,
  deleteResult,
  getResultByShortCode,
  listResults,
  searchAnalytics,
  searchResult,
  toggleResult
} from '../controllers/resultController.js';
import { authRequired } from '../middleware/auth.js';

const router = Router();

router.get('/search', searchResult);
router.get('/short/:shortCode', getResultByShortCode);
router.get('/analytics', authRequired, searchAnalytics);
router.get('/', authRequired, listResults);
router.post('/', authRequired, createResult);
router.post('/bulk', authRequired, bulkInsertResults);
router.patch('/:id/toggle', authRequired, toggleResult);
router.delete('/:id', authRequired, deleteResult);

export default router;
