const S = require("fluent-json-schema");
const Base = require("../../config/Base");

module.exports = S.object().additionalProperties(false)
  .prop('question', S.string())
  .prop('type', S.string().enum(Base.getQuestionTypesAsArray()))
  .prop('options', S.array().items(S.mixed(['string', 'boolean', 'number'])).minItems(2))
  .prop('answer', S.mixed(['boolean', 'number', 'string']))
  .prop('answers', S.array())
  .prop('point', S.number().minimum(1))
  .prop('mpoint', S.number().minimum(0))
  .prop('explanation', S.string())
  .prop('settings', S.object());

