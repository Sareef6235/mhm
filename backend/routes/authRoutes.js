const router = require('express').Router();
const { login } = require('../controllers/authController');
const validate = require('../middleware/validate');
const { loginSchema } = require('../validations/authValidation');

router.post('/login', validate(loginSchema), login);

module.exports = router;
