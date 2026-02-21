import Joi from 'joi';

export const subjectSchema = Joi.object({
  name: Joi.string().trim().min(1).required(),
  mark: Joi.number().min(0).required()
});

export const resultSchema = Joi.object({
  registerNumber: Joi.string().trim().required(),
  name: Joi.string().trim().required(),
  school: Joi.string().trim().required(),
  photo: Joi.string().uri().allow('', null),
  dob: Joi.date().iso().required(),
  subjects: Joi.array().items(subjectSchema).min(1).required()
});
