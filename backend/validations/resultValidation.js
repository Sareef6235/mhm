const Joi = require('joi');

const subjectSchema = Joi.object().pattern(Joi.string(), Joi.number().min(0).required()).required();

const studentSchema = Joi.object({
  register_number: Joi.string().required(),
  name: Joi.string().required(),
  dob: Joi.date().iso().optional().allow(null, ''),
  photo_url: Joi.string().uri().optional().allow(null, ''),
  subjects: subjectSchema
});

const createExamSchema = Joi.object({
  title: Joi.string().required(),
  total_marks: Joi.number().integer().min(1).required(),
  pass_mark: Joi.number().integer().min(0).required(),
  grading_config: Joi.array().items(Joi.object({ min: Joi.number().required(), grade: Joi.string().required() })).required(),
  is_active: Joi.boolean().default(true)
});

const searchSchema = Joi.object({
  register_number: Joi.string().required(),
  exam_id: Joi.number().integer().required(),
  dob: Joi.date().iso().optional()
});

module.exports = { studentSchema, createExamSchema, searchSchema };
