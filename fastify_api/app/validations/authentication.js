const S = require('fluent-json-schema');
const Base = require('../config/Base');


exports.register  = {
  body: S.object()
  .additionalProperties(false)
  .prop('first_name', S.string().required())
  .prop('last_name', S.string().required())
  .prop('email', S.string().format(S.FORMATS.EMAIL).required())
  .prop('password', S.string().required())
  .prop('confirm_password', S.string().required())
  .prop('referral', S.string().default(''))
  .prop('mobile', S.string().required())
  .prop('type', S.string().enum(['admin', 'member']).required())
  
};

exports.anonymous = {
  body: S.object()
  .additionalProperties(false)
  .prop('type', S.string().const(Base.getUserType('type4')).required())
  .prop('name', S.string().required())
  .prop('status', S.string().default('approved'))
};

exports.verify = {
  body: S.object()
  .additionalProperties(false)
  .prop('user_id', S.string().required())
  .prop('key', S.number().required())
};

exports.login = {
  body: S.object()
  .additionalProperties(false)
  .prop('username', S.string().required())
  .prop('password', S.string().required())
};

exports.forgot = {
  body: S.object()
  .additionalProperties(false)
  .prop('email', S.string().format(S.FORMATS.EMAIL).required())
};

exports.otp = {
  body: S.object()
  .additionalProperties(false)
  .prop('otp', S.number().required())
  .prop('email', S.string().format(S.FORMATS.EMAIL).required())
};

exports.newPassword = {
  body: S.object()
  .additionalProperties(false)
  .prop('otp', S.number().required())
  .prop('email', S.string().format(S.FORMATS.EMAIL).required())
  .prop('new_password', S.string().required())
  .prop('confirm_password', S.string().required())
};

exports.quizzes = {};
exports.quizzes.save = {
  body: S.object()
  .additionalProperties(false)
  .prop('quiz_id', S.string().required())
  .prop('time', S.number().required())
  .prop('questions', S.array().items(S.object()
  .additionalProperties(false)
  .prop('number', S.number().minimum(1).required())
  .prop('time', S.number().minimum(0).required())
  .prop('attempted', S.boolean().required())
  .prop('question_id', S.string().required())
  .prop('answer', S.mixed(['string', 'number', 'array', 'boolean']).required())
  
  
  ).required())
}