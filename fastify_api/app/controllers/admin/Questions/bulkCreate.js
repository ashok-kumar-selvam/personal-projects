const { default: S } = require("fluent-json-schema");
const Question = require("../../../helpers/standardObjectSchemas/Question");
const UploadHelper = require('../../../helpers/Uploads/UploadHelper');

module.exports = {

  schema: {
    body: S.object().additionalProperties(false)
      .prop('entity_id', S.string().required())
      .prop('questions', S.array().items(S.object().required(['question', 'type', 'point', 'mpoint']).extend(Question)).required()),

    response: {
      200: S.string().const('success'),
    }
  },

  async handler(req, res) {
    try {
      const {body} = req;
      const user_id = new this.mongo.ObjectId(req.user.admin_id ?? req.user.uuid);
      const users = this.mongo.db.collection('users');
      const exams = this.mongo.db.collection('exams');
      const quizzes = this.mongo.db.collection('quizzes');
      const questions = this.mongo.db.collection('questions');
      const questionPaper = this.mongo.db.collection('questionPaper');
      const source_id = new this.mongo.ObjectId(body.entity_id);
      const source = await exams.findOne({_id: source_id}) ? 'exam': await quizzes.findOne({_id: source_id}) ? 'quiz': 'unknown';
      const [result, values] = UploadHelper.validate(body.questions);
      console.log('the values is ', values);
      if(!result)
        return res.status(400).send(values);
      
      const preparedQuestions = values.map(q => ({...q, source, source_id, user_id}));
      const {insertedIds} = await questions.insertMany(preparedQuestions);
      const idsList = Object.keys(insertedIds).map(key => ({entity_id: source_id, question_id: insertedIds[key]}));
      const {insertedCount} = await questionPaper.insertMany(idsList);
      return insertedCount == idsList.length ? res.send('success'): res.status(400).send(`We were able to upload ${insertedCount} questions. `);
    } catch(error) {
      console.log(error);
      return res.status(400).send('error occured');
    }
  }
}