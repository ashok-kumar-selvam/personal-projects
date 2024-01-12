const { default: S } = require("fluent-json-schema");

module.exports = S.object().additionalProperties(false)
  .prop('status', S.string().enum(['active', 'inactive']).default('inactive'))
  .prop('exam_id', S.string())

  .prop('introduction', S.object().additionalProperties(false)
    .prop('name', S.string())
    .prop('instructions', S.array().items(
      S.object().additionalProperties(false)
      .prop('title', S.string())
      .prop('text', S.string())
    ).required())
  )

  .prop('assignee', S.object().additionalProperties(false)
    .prop('assign_to', S.string().enum(['allMembers', 'singleMember', 'selectedMembers', 'allGroups', 'singleGroup', 
    'selectedGroups', 'anonymousUsers']).default('allMembers'))
    .prop('assignee_id', S.string())
    .prop('limit', S.number().default(0))
    .prop('anonymousEmails', S.array().items(S.string().format(S.FORMATS.EMAIL)).default([]))
  )

  .prop('time', S.object().additionalProperties(false)
    .prop('start', S.number().default(Math.floor(Date.now()/1000)))
    .prop('end', S.number().default(0))
    .prop('duration', S.number().default(0))
  )

  .prop('question', S.object().additionalProperties(false)
    .prop('random', S.string().enum(['yes', 'no']).default('yes'))
    .prop('options', S.string().enum(['default', 'random']).default('default'))
    .prop('time', S.number().default(0))
    .prop('mpoint', S.number().maximum(100).default(0))
    .prop('previousQuestion', S.string().enum(['enable', 'disable']).default('enable'))
    .prop('showAnswer', S.string().enum(['yes', 'no']).default('no'))
    .prop('showExplanation', S.string().enum(['yes', 'no']).default('no'))
  
  )

  .prop('result', S.object().additionalProperties(false)
    .prop('type', S.string().enum(['pass_or_fail', 'simple_result', 'complete_result'])
    .default('complete_result'))
    .prop('method', S.string().enum(['immediate', 'scheduled', 'manual'])
    .default('immediate'))
    .prop('showAnswer', S.string().enum(['yes', 'no']).default('no'))
    .prop('showExplanation', S.string().enum(['yes', 'no']).default('no'))
    .prop('publishOn', S.string())
  
  )

  .prop('general', S.object().additionalProperties(false)
    .prop('attempts', S.number().minimum(0).default(0))
    .prop('time', S.number().default(0))
    .prop('resumable', S.enum(['yes', 'no']).default('no'))
    .prop('passmark', S.number().minimum(0).maximum(100).default(40))
  )
  