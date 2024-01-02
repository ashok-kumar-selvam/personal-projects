const S = require('fluent-json-schema');

module.exports = {

  schema: {
    params: S.object().prop('result_id', S.string().required()),

    response: {
      200: S.object()
      .prop('quiz_id', S.string())
      .prop('member_id', S.string())
      .prop('member_type', S.string())
      .prop('attempt', S.number())
      .prop('name', S.string())
      .prop('title', S.string())

      .prop('stats', S.object()
        .prop('total_questions', S.number())
        .prop('attempted_questions', S.number())
        .prop('answered_questions', S.number())
        .prop('correct_answers', S.number())
        .prop('total_points', S.number())
        .prop('taken_points', S.number())
        .prop('taken_time', S.number())
      )
      .prop('questions', S.array().items(
        S.object()
        .prop('number', S.number())
        .prop('question_id', S.string())
        .prop('type', S.string())
        .prop('question', S.string())
        .prop('options', S.array())
        .prop('correctAnswer', S.mixed(['string', 'number', 'boolean', 'array']))
        .prop('answer', S.mixed(['string', 'number', 'boolean', 'array']))
        .prop('time', S.number())
        .prop('isCorrect', S.boolean())
        .prop('point', S.number())
      ))
    }
  },

  async handler(req, res) {
    try {
      const _id = new this.mongo.ObjectId(req.params.result_id);
      const db = this.mongo.db;
      const result = await db.collection('results').findOne({_id});
      if(!result)
        return res.status(404).send('Unable to find the result. ');
  
      const quiz = await db.collection('quizzes').findOne({_id: new this.mongo.ObjectId(result.quiz_id)});
      const member = await db.collection('users').findOne({_id: new this.mongo.ObjectId(result.member_id)});
      result.title = quiz.title;
      result.name = member.name;
      return res.send(result);
  
    } catch(error) {
      console.error(error);
      return res.status(500).send('Error Occured');
    }
  }
}