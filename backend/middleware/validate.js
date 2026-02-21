module.exports = (schema, source = 'body') => (req, res, next) => {
  const { error, value } = schema.validate(req[source], { abortEarly: false, stripUnknown: true });
  if (error) {
    return res.status(422).json({ message: 'Validation failed', details: error.details.map((d) => d.message) });
  }

  req[source] = value;
  return next();
};
