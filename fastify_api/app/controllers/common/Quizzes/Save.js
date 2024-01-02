const S = require('fluent-json-schema');
const {getUser} = require('../../../helpers/UserHelper');
const {correction, getStats, mergeArrays} = require('../../../helpers/QuizHelper');

module.exports = {

  schema: {

    body: S.object().additionalProperties(false)
    .prop('quiz_id', S.string().required())
    .prop('time', S.number().required())
    .prop('questions', S.array().items(S.object().additionalProperties(false)
      .prop('number', S.number().minimum(1).required())
      .prop('time', S.number().minimum(0).required())
      .prop('attempted', S.boolean().required())
      .prop('question_id', S.string().required())
      .prop('answer', S.mixed(['string', 'number', 'array', 'boolean']).required())
  ).required()),

  response: {
    200: S.object().prop('id', S.string())
  }
  },
  
  async handler(req, res) {
    try {
      let data = req.body;
      const user = await getUser(req);
      
      const quiz_id = new this.mongo.ObjectId(data.quiz_id);
      const member_id = new this.mongo.ObjectId(user.uuid);
      
      const questions = await this.mongo.db.collection('questions').aggregate([
      { $lookup: {
        from: 'questionPaper',
        localField: '_id',
        foreignField: 'question_id',
        as: 'questionPaper'
      }},
      { $match: {
        'questionPaper.entity_id': new this.mongo.ObjectId(data.quiz_id)
      }}, 
      { $project: {
        _id: 0, question_id: '$_id', options: 1, type: 1, question: 1,
        correctAnswer: {$ifNull: ['$answers', '$answer']}
      }}]).toArray();
      
      if(questions.length != data.questions.length)
        return res.status(400).send('The questions has been modified during the exam from the admin side. Unable to do the correction.');
      
      const resultQuestions = mergeArrays(questions, data.questions);
      const correctedQuestions = resultQuestions.map(correction);
      let stats = getStats(correctedQuestions);
      stats.taken_time = data.time;
      const result = {
        type: 'quiz',
        quiz_id: quiz_id,
        member_id: member_id,
        member_type: user.type,
        attempt: await this.mongo.db.collection('results').countDocuments({quiz_id, member_id})+1,
        stats: stats,
        questions: correctedQuestions,
        created_at: Date.now(),
      };
      const insert  = await this.mongo.db.collection('results').insertOne(result);
      
      return {id: insert.insertedId};
      
      
    } catch(error) {
      return res.send(error);
      
    }
  }
}