const Joi = require('joi');
const  Base = require('../../config/Base');
const questionTypes = Base.getQuestionTypesAsArray();
const common = Joi.object({
  type: Joi.string().valid(...questionTypes).required(),
  point: Joi.number().min(1).default(1),
  mpoint: Joi.number().min(0).max(Joi.ref('point')).default(0),
  explanation: Joi.string().default(''),
  question: Joi.string().min(3).required(),
  settings: Joi.object().default({}),
});

exports.type1 = common.append({
  type: Joi.string().valid(Base.getQuestionTypeName('type1')),
  options: Joi.array().items(Joi.string(), Joi.number()).min(2).required(),
  answer: Joi.alternatives().try(Joi.string().valid(Joi.in('options')), Joi.number().valid(Joi.in('options'))).required()
});

exports.type2 = common.append({
  type: Joi.string().valid(Base.getQuestionTypeName('type2')).required(),
  options: Joi.array().items(Joi.boolean()).length(2).required(),
  answer: Joi.boolean().valid(Joi.in('options')).required()
});

exports.type3 = common.append({
  type: Joi.string().valid(Base.getQuestionTypeName('type3')),
  options: Joi.array().items(Joi.string(), Joi.number(), Joi.boolean()).min(2).required(),
  answers: Joi.array().items(Joi.any()).min(1).custom((value, helpers) => {
    const {options} = helpers.state.ancestors[0];
    return value.every(item => options.includes(item)) ? value: helpers.message('The answers should be present in options.');
  }).required()
}).label('multi choice');

