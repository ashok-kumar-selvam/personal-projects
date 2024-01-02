import Joi from "joi";

const Schemas = {
  validate: Joi.string().required(),
  start: Joi.string().required(),
  submit: Joi.string().required(),
  allQuestions: Joi.string().valid('all', 'marked', 'answered', 'attempted'),
  answer: Joi.object({
    id: Joi.string().required(),
    answer: Joi.any().required(),
  }),
  mark: Joi.object({
    id: Joi.string().required(),
    marked: Joi.string().valid('yes', 'no').required()
  }),
  question: Joi.object({
    id: Joi.string().required(),
    action: Joi.string().valid('current', 'next', 'previous').required(),
    answer_id: Joi.string()
  }),

}

export default Schemas;