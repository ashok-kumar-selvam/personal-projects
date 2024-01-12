const S = require('fluent-json-schema')
const questionSchema = require('../../../helpers/standardObjectSchemas/Question');

module.exports = {

  schema: {
    params: S.object().additionalProperties(false)
      .prop('question_id', S.string().required()),

    response: {
      200: S.object()
        .prop('id', S.string())
        .required(['id', 'question', 'type', 'point', 'mpoint'])
        .extend(questionSchema)
        
    }
  },

  async handler(req, res) {
    try {
      const _id = new this.mongo.ObjectId(req.params.question_id);
      const question = await this.mongo.db.collection('questions').findOne({_id}, { projection: {
        _id: 0, id: '$_id',
        question: 1, type: 1, options: 1, answer: 1, answers: 1,
        settings: 1, point: 1, mpoint: 1, explanation: 1,
        
      }});
      if(!question)
        return res.status(404).send('Unable to find the question. ');
      
      return question;
    } catch(error) {
      return res.status(400).send(error.message);
    }
  }
  
}