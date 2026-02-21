const router = require('express').Router();
const auth = require('../middleware/auth');
const validate = require('../middleware/validate');
const { listExams, createExam, dashboardSummary } = require('../controllers/examController');
const { createExamSchema } = require('../validations/resultValidation');

router.get('/', listExams);
router.post('/', auth, validate(createExamSchema), createExam);
router.get('/dashboard/summary', auth, dashboardSummary);

module.exports = router;
