const xss = require('xss');

const sanitizeObject = (obj) => {
  if (typeof obj === 'string') return xss(obj);
  if (Array.isArray(obj)) return obj.map(sanitizeObject);
  if (obj && typeof obj === 'object') {
    return Object.keys(obj).reduce((acc, key) => {
      acc[key] = sanitizeObject(obj[key]);
      return acc;
    }, {});
  }
  return obj;
};

module.exports = (req, _res, next) => {
  req.body = sanitizeObject(req.body);
  req.query = sanitizeObject(req.query);
  return next();
};
