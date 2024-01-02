const S = require('fluent-json-schema');

exports.exams = {};
exports.exams.create = {
  body: S.object()
  .additionalProperties(false)
  .prop('title', S.string().required())
  .prop('category', S.string().default('general'))
  .prop('description', S.string())
};

exports.exams.update = {
  body: S.object()
  .additionalProperties(false)
  .prop('id', S.string())
  .prop('title', S.string())
  .prop('category', S.string())
  .prop('description', S.string())
};

exports.questions = {};

exports.questions.getcount = {
  query: S.object()
  .prop('entity_id', S.string().required())
};

exports.questions.create = {
  body: S.object()
  .additionalProperties(false)
  .prop('user_id', S.string())
  .prop('source', S.string().enum(['exam', 'quiz']).required())
  .prop('source_id', S.string().required())
  .prop('question', S.string().required())
  .prop('point', S.number().minimum(1).required())
  .prop('mpoint', S.number().minimum(0).required())
  .prop('explanation', S.string().default(''))
  .prop('settings', S.object())
  .prop('type', S.string().enum(['single_choice', 'multi_choice', 'true_or_false',
  'match_it', 'fill_the_blanks', 'descriptive', 'cloze', 'error_correction'])
  .required())
  .prop('options')
  .prop('answer')
  
  .ifThen(
  S.object().prop('source', S.string().const('quizzes')),
  S.object().prop('type', S.string().enum(['single_choice', 'multi_choice', 'true_or_false']).required())
  )
  
  .ifThen(S.object().prop('type', S.string().const('single_choice')),
  S.object().prop('options', S.array().minItems(2).required())
  .prop('answer', S.string().required()))
  
  .ifThen(
  S.object().prop('type', S.string().const('multi_choice')),
  S.object().prop('options', S.array().minItems(2).required())
  .prop('answers', S.array().minItems(1).required())
  )
  
  .ifThen(
  S.object().prop('type', S.string().const('true_or_false')),
  S.object().prop('options', S.array().enum(['true', 'false']).required())
  .prop('answer', S.string().enum(['true', 'false']).required())
  )
  
  .ifThen(
  S.object().prop('type', S.string().const('match_it')),
  S.object().prop('answer', S.array().items(
  S.object().prop('question', S.string())
  .prop('answer', S.string())
  ).minItems(2).required())
  )
  
  .ifThen(
  S.object().prop('type', S.string().const('fill_the_blanks')).prop('type', S.string().const('error_correction')),
  S.object().prop('answer', S.array().minItems(1).required())
  )
  
  .ifThen(
  S.object().prop('type', S.string().const('cloze')),
  S.object().prop('options', S.string().required())
  .prop('answer', S.array().minItems(1).required())
  )
}

exports.questions.bulkCreate = {
  body: S.object()
  .additionalProperties(false)
  .prop('entity_id', S.string().required())
  .prop('questions', S.array().minItems(1).required())
}

exports.questions.update = {
  body: S.object()
  .additionalProperties(false)
  .prop('question', S.string())
  .prop('point', S.number().minimum(1))
  .prop('mpoint', S.number().minimum(0))
  .prop('explanation', S.string())
  .prop('settings', S.object())
  .prop('type', S.string().enum(['single_choice', 'multi_choice', 'true_or_false',
  'match_it', 'fill_the_blanks', 'descriptive', 'cloze', 'error_correction'])
  .required())
  .prop('options')
  .prop('answer')
  
  .ifThen(
  S.object().prop('source', S.string().const('quizzes')),
  S.object().prop('type', S.string().enum([
  'single_choice', 'multi_choice', 'true_or_false']).required())
  
  )
  
  .ifThen(S.object().prop('type', S.string().const('single_choice')),
  S.object().prop('options', S.array().minItems(2).required())
  .prop('answer', S.string().required()))
  
  .ifThen(
  S.object().prop('type', S.string().const('multi_choice')),
  S.object().prop('options', S.array().minItems(2).required())
  .prop('answer', S.array().minItems(1).required())
  )
  
  .ifThen(
  S.object().prop('type', S.string().const('true_or_false')),
  S.object().prop('options', S.array().enum(['true', 'false']).required())
  .prop('answer', S.string().enum(['true', 'false']).required())
  )
  
  .ifThen(
  S.object().prop('type', S.string().const('match_it')),
  S.object().prop('answer', S.array().items(
  S.object().prop('question', S.string())
  .prop('answer', S.string())
  ).minItems(2).required())
  )
  
  .ifThen(
  S.object().prop('type', S.string().const('fill_the_blanks')).prop('type', S.string().const('error_correction')),
  S.object().prop('answer', S.array().minItems(1).required())
  )
  
  .ifThen(
  S.object().prop('type', S.string().const('cloze')),
  S.object().prop('options', S.string().required())
  .prop('answer', S.array().minItems(1).required())
  )
}

exports.questions.remove = {
  body: S.object()
  .prop('entity_id', S.string().required())
  .prop('question_id', S.string().required())
}

exports.questions.add = {
  body: S.array()
  .minItems(1)
}

exports.questions.upload = {
  consumes: ['multipart/form-data'],
  body: S.object()
  .additionalProperties(false)
  .prop('questions', S.object().required())
  .prop('entity_id', S.string().required())
  .prop('question_count', S.number().minimum(1).required())
  .prop('file_type', S.string().enum(['xlsx', 'txt']).required())
}
exports.quizzes = {};

exports.quizzes.create = {
  consumes: ['multipart/form-data'],
  body: S.object()
  .additionalProperties(false)
  .prop('title', S.string().required())
  .prop('category', S.string().default('General'))
  .prop('notes', S.string())
  .prop('validity', S.string().enum(['always', 'untill']).default('always'))
  .prop('expires_on', S.string().format(S.FORMATS.DATE))
  .prop('member_only', S.string().enum(['yes', 'no']).default('no'))
  .prop('publish', S.string().enum(['yes', 'no']).default('no'))
  
  .ifThen(
  S.object().prop('validity', S.string().const('untill')),
  S.object().prop('expires_on', S.string().format(S.FORMATS.DATE).required())
  )
}

exports.quizzes.update = {
  
  body: S.object()
  .additionalProperties(false)
  .prop('title', S.string())
  .prop('category', S.string())
  .prop('notes', S.string())
  .prop('validity', S.string().enum(['always', 'untill']))
  .prop('expires_on', S.string())
  .prop('member_only', S.string().enum(['yes', 'no']))
  .prop('publish', S.string().enum(['yes', 'no']))
  
  
}


exports.groups = {};
exports.groups.create = {
  body: S.object()
  .additionalProperties(false)
  .prop('name', S.string().required())
  .prop('description', S.string().required())
}

exports.groups.update = {
  body: S.object()
  .additionalProperties(false)
  .prop('name', S.string())
  .prop('description', S.string())
}

exports.groups.addMembers = {
  body: S.array()
  .items(S.string())
  .minItems(1)
}

exports.groups.removeMember = {
  query: S.object()
  .prop('member_id', S.string().required())
}

const createSettings  = {
  introduction: S.object()
  .additionalProperties(false)
  .prop('name', S.string())
  .prop('instructions', S.array().items(
  S.object()
  .prop('title', S.string())
  .prop('text', S.string().required()))),
  
  assignee: S.object()
  .additionalProperties(false)
  .prop('assign_to', S.string()
  .enum(['allMembers', 'singleMember', 'selectedMembers', 'allGroups', 'singleGroup', 
  'selectedGroups', 'anonymousUsers']).required())
  
  .prop('assignee_id', S.string())
  .prop('limit', S.number().default(0))
  .prop('anonymousEmails', S.array().items(S.string().format(S.FORMATS.EMAIL)).default([])),
  
  time: S.object()
  .additionalProperties(false)
  .prop('start', S.number().default(0))
  .prop('end', S.number().default(0))
  .prop('duration', S.number().default(0)),
  
  question: S.object()
  .additionalProperties(false)
  .prop('random', S.string().enum(['yes', 'no']).default('yes'))
  .prop('options', S.string().enum(['default', 'random']).default('default'))
  .prop('time', S.number().default(0))
  .prop('mpoint', S.number().maximum(100).default(0))
  .prop('previousQuestion', S.string().enum(['enable', 'disable']).default('enable'))
  .prop('showAnswer', S.string().enum(['yes', 'no']).default('no'))
  .prop('showExplanation', S.string().enum(['yes', 'no']).default('no')),
  
  result: S.object()
  .additionalProperties(false)
  .prop('type', S.string().enum(['pass_or_fail', 'simple_result', 'complete_result'])
  .default('complete_result'))
  .prop('method', S.string().enum(['immediate', 'scheduled', 'manual'])
  .default('immediate'))
  .prop('showAnswer', S.string().enum(['yes', 'no']).default('no'))
  .prop('showExplanation', S.string().enum(['yes', 'no']).default('no'))
  .prop('publishOn', S.string()),
  
  general: S.object()
  .additionalProperties(false)
  .prop('attempts', S.number().minimum(0).default(0))
  .prop('time', S.number().default(0))
  .prop('resumable', S.enum(['yes', 'no']).default('no'))
  .prop('passmark', S.number().minimum(0).maximum(100).default(40)),
  
  
  
}


exports.assignments  = {};
exports.assignments.create = {
  body: S.object()
  .additionalProperties(false)
  .prop('status', S.string().enum(['active', 'inactive']).default('inactive'))
  .prop('exam_id', S.string().required())
  .prop('introduction', createSettings.introduction)
  .prop('assignee', createSettings.assignee)
  .prop('time', createSettings.time)
  .prop('question', createSettings.question)
  .prop('result', createSettings.result)
  .prop('general', createSettings.general)
  .required(['introduction', 'assignee', 'question', 'general', 'time', 'result'])
}

exports.assignments.edit = {
  params: S.object()
  .additionalProperties(false)
  .prop('assign_id', S.string().required())
  .prop('segment', S.string().enum(['introduction', 'assignee', 'question', 'time', 'general', 'result']).required())
  
}

exports.assignments.update = {
  params: S.object()
  .additionalProperties(false)
  .prop('assign_id', S.string().required())
  .prop('segment', S.string().enum(['introduction', 'assignee', 'question', 'time', 'general', 'result']).required()),
  body: S.object()
  .additionalProperties(false)
  .prop('introduction', S.object()
  .additionalProperties(false)
  .prop('name', S.string())
  .prop('instructions', S.array().items(S.object().prop('title', S.string()).prop('text', S.string()))))
  
  .prop('assignee', S.object()
  .additionalProperties(false)
  .prop('assign_to', S.string().enum(['singleMember', 'allMembers', 'singleGroup', 'allGroup', 'selectedMembers', 'selectedGroups', 'anonymousUsers']).required())
  .prop('assignee_id', S.string())
  .prop('limit', S.number())
  .prop('anonymousEmails', S.array().items(S.string().format(S.FORMATS.EMAIL))))
  
  .prop('question', S.object()
  .additionalProperties(false)
  .prop('random', S.string().enum(['yes', 'no']))
  .prop('options', S.string().enum(['random', 'default']))
  .prop('time', S.number())
  .prop('previousQuestion', S.string().enum(['enable', 'disable']))
  .prop('showAnswer', S.string().enum(['yes', 'no']))
  .prop('showExplanation', S.string().enum(['yes', 'no']))
  .prop('mpoint', S.number()))
  
  .prop('time', S.object()
  .additionalProperties(false)
  .prop('start', S.number())
  .prop('end', S.number())
  .prop('duration', S.number()))
  
  .prop('general', S.object()
  .additionalProperties(false)
  .prop('attempts', S.number())
  .prop('resumable', S.string().enum(['yes', 'no']))
  .prop('passmark', S.number().minimum(0).maximum(100)))
  
  .prop('result', S.object()
  .additionalProperties(false)
  .prop('type', S.string().enum(['complete_result', 'pass_or_fail', 'simple_result']))
  .prop('method', S.string().enum(['immediate', 'scheduled', 'manual']))
  .prop('publishOn', S.number())
  .prop('showAnswer', S.string().enum(['yes', 'no']))
  .prop('showExplanation', S.string().enum(['yes', 'no'])))
}


exports.assignments.activate = {
  body: S.object()
  .additionalProperties(false)
  .prop('status', S.string().enum(['active', 'inactive']).required())
}

exports.assignments.publish = {
  body: S.object()
  .additionalProperties(false)
  .prop('published', S.string().enum(['yes', 'no']).required())
}

exports.instructors = {};
const actions  = ['view', 'create', 'edit', 'delete'];
exports.instructors.create = {
  body: S.object()
  .additionalProperties(false)
  .prop('name', S.string().minLength(4).required())
  .prop('email', S.string().format(S.FORMATS.EMAIL).required())
  
  .prop('permissions', S.object()
  .additionalProperties(false)
  .prop('exams', S.array().items(S.string().enum(actions)).default([]))
  .prop('quizzes', S.array().items(S.string().enum(actions)).default([]))
  .prop('members', S.array().items(S.string().enum(actions)).default([]))
  .prop('groups', S.array().items(S.string().enum(actions)).default([]))
  .prop('assignments', S.array().items(S.string().enum(actions)).default([]))
  .prop('questions', S.array().items(S.string().enum(actions)).default([]))
  .prop('results', S.array().items(S.string().enum(actions)).default([]))
  )
}

exports.instructors.update = {
  body: S.object()
  .additionalProperties(false)
  .prop('name', S.string().minLength(4))
  .prop('email', S.string().format(S.FORMATS.EMAIL))
.prop('status', S.string().enum(['active', 'suspended']))  
  .prop('permissions', S.object()
  .additionalProperties(false)
  .prop('exams', S.array().items(S.string().enum(actions)))
  .prop('quizzes', S.array().items(S.string().enum(actions)))
  .prop('members', S.array().items(S.string().enum(actions)))
  .prop('groups', S.array().items(S.string().enum(actions)))
  .prop('assignments', S.array().items(S.string().enum(actions)))
  .prop('questions', S.array().items(S.string().enum(actions)))
  .prop('results', S.array().items(S.string().enum(actions)))
  )
}


exports.members = {};
exports.members.create = {
  body: S.object()
  .additionalProperties(false)
  .prop('first_name', S.string().required())
  .prop('email', S.string().format(S.FORMATS.EMAIL).required())
  .prop('password', S.string().default(''))
  .prop('mobile', S.string().required())
}

exports.members.upload = {
  consumes: ['multipart/form-data'],
  body: S.object()
  
  .prop('members', S.string())
}

exports.members.code = {
  query: S.object()
  .additionalProperties(false)
  .prop('expiresOn', S.number().default(0))
  .prop('limit', S.number().default(0))
}

exports.members.update = {
  body: S.object()
  .additionalProperties(false)
  .prop('status', S.string().enum(['approved', 'suspended']).required())
}