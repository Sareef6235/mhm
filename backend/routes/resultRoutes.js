const router = require('express').Router();
const rateLimit = require('express-rate-limit');
const auth = require('../middleware/auth');
const { searchSchema } = require('../validations/resultValidation');
const validate = require('../middleware/validate');
const {
  searchResult,
  bulkUploadStudents,
  getShortLink,
  manageLinks,
  toggleLink,
  saveCertificateTemplate,
  verifyCertificate,
  importFromGoogleSheet
} = require('../controllers/resultController');

const searchLimiter = rateLimit({ windowMs: 60 * 1000, max: 20, standardHeaders: true });

router.get('/search', searchLimiter, validate(searchSchema, 'query'), searchResult);
router.post('/students/upload', auth, bulkUploadStudents);
router.get('/short/:shortcode', getShortLink);
router.get('/links', auth, manageLinks);
router.patch('/links/:id/toggle', auth, toggleLink);
router.post('/certificate-template', auth, saveCertificateTemplate);
router.get('/verify-certificate', verifyCertificate);
router.post('/import/google-sheet/preview', auth, importFromGoogleSheet);

module.exports = router;
