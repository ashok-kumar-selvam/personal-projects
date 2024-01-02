const S = require('fluent-json-schema');
const Base = require('../../../config/Base');
const schemas = require('../../../helpers/Uploads/UploadQuestionSchemas');

module.exports = {

  schema: {
    body: S.object()
  .additionalProperties(false)
  
  .prop('source', S.string().enum(['exam', 'quiz']).required())
  .prop('source_id', S.string().required())
  .prop('question', S.string().required())
  .prop('point', S.number().minimum(1).required())
  .prop('mpoint', S.number().minimum(0).required())
  .prop('explanation', S.string().default(''))
  .prop('settings', S.object())
  .prop('type', S.string().enum(Base.getAllSupportedQuestionTypes()).required())
  .prop('options', S.array().minItems(2))
  .prop('answer', S.mixed(['string', 'boolean', 'number']))
  .prop('answers', S.array().minItems(1))
  
  .ifThen(
  S.object().prop('source', S.string().const('quiz')),
  S.object().prop('type', S.string().enum(Base.getSupportedQuestionTypesByEntity('quiz')).required())
  ),

  response: {
    200: S.object()
    .prop('count', S.number())
    .prop('success', S.boolean())
  }
  },

  async handler(req, res) {
    try {
      const questions = this.mongo.db.collection('questions');
      const questionPaper = this.mongo.db.collection('questionPaper');
      const ObjectId = this.mongo.ObjectId;
      let  {body} = req;
 
      const {error, values} = schemas[Base.getQuestionTypeKey(body.type)].validate(body, {stripUnknown: true});
      if(error)
        return res.status(400).send(error.details[0].message);

      values.created_at = Date.now();
      values.source_id =  new ObjectId(body.source_id);
      values.user_id = new ObjectId(req.user.admin_id || req.user.uuid);
      
      const result = await questions.insertOne(values);
      const result2 = await questionPaper.insertOne({
        entity_id: new ObjectId(values.source_id),
        question_id: result.insertedId
      });
      const count = await questionPaper.countDocuments({entity_id: new ObjectId(values.source_id)});
      
      return res.send({count, 'success': true});
    } catch(error) {
      return res.status(400).send(error.message);
    }
  }
}