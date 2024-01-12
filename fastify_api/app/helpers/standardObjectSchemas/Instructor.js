const { default: S } = require("fluent-json-schema");
const actions = S.string().enum(['create', 'edit', 'view', 'delete']);
module.exports = S.object().additionalProperties(false)
.prop('name', S.string())
.prop('email', S.string().format(S.FORMATS.EMAIL))
.prop('status', S.string().enum(['active', 'suspended']))
.prop('permissions', 
  S.object().additionalProperties(false)
  .prop('exams', actions)
  .prop('quizzes', actions)
  .prop('groups', actions)
  .prop('members', actions)
  .prop('assignments', actions)
  .prop('questions', actions)
  .prop('results', actions)

)