const {default: S} = require('fluent-json-schema');
const Question = require('../../../helpers/standardObjectSchemas/Question');
const {XlsxUploadHelper, TxtUploadHelper, UploadHelper} = require('../../../helpers');

module.exports = {
  schema: {
    consumes: ['multipart/form-data'],
    body: S.object()
      .additionalProperties(false)
      .prop('questions', S.object().required())
      .prop('entity_id', S.string().required())
      .prop('question_count', S.number().minimum(1).required())
      .prop('file_type', S.string().enum(['xlsx', 'txt']).required()),

    response: {
      200: S.object().additionalProperties(false)
        .prop('questions', S.array().items(S.object().prop('number', S.number()).required(['number', 'question', 'type', 'point', 'mpoint']).extend(Question)).minItems(1).required()),

    }
  },

  async handler(req, res) {
    try {
      const data = req.body;
      const entity_id = new this.mongo.ObjectId(data.entity_id);
      const entityType = await this.mongo.db.collection('exams').findOne({_id: entity_id}) ?
        'exam':
        await this.mongo.db.collection('quizzes').findOne({_id: entity_id}) ? 'quiz': 'unknown';
      
      if(data.file_type == 'xlsx') {
        const xlUpload = new XlsxUploadHelper(data.question_count, data.file_type, entityType, data);
  
        if(!xlUpload.prepare())
          return res.status(400).send(xlUpload.getError());
  
        return res.send({
          questions: xlUpload.getQuestions(),
  
        });
      } else if(data.file_type == 'txt') {
        const txtUpload = new TxtUploadHelper(data.question_count, data.file_type, entityType, data);
        if(!txtUpload.prepare())
          return res.status(400).send(txtUpload.getError());
        return res.send({
          questions: txtUpload.getQuestions(),
        });
      }
      return res.status(400).send('Unexpected file type error. ')
    } catch(error) {
      return res.send(error);
    }
  }
}