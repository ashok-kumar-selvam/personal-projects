const S = require('fluent-json-schema');

module.exports = {

  schema: {
    params: S.object().prop('quiz_id', S.string().required()),

    response: {
      200: S.object().additionalProperties(false)
      .prop('quiz', S.object()
        .prop('id', S.string().required())
        .prop('title', S.string().required())
        .prop('category', S.string())
        .prop('notes', S.string())
        .prop('expiry', S.string())
        .prop('expires_on', S.string())
        .prop('publish', S.string())
        .prop('member_only', S.string()).required()
      )
      .prop('questions', S.array().items(
        S.object()
        .prop('id', S.string())
        .prop('question', S.string())
        .prop('type', S.string())
        .prop('options', S.array())
        .prop('answer', S.string())
        .prop('answers', S.array())
        .prop('point', S.number())
        .prop('mpoint', S.number())
        .prop('explanation', S.string())
      ).required())
    }
  },

  async handler(req, res) {
    try {
      const quiz = await this.mongo.db.collection('quizzes').findOne({_id:
      new this.mongo.ObjectId(req.params.quiz_id)}, {projection: {
        _id: 0, id: '$_id',
        title: 1, category: 1, expires_on:1, notes: 1, expiry: 1, member_only: 1, publish: 1,
      }});
      
      const questions = await this.mongo.db.collection('questions').aggregate([
      {$lookup: {
        from: 'questionPaper',
        localField: '_id',
        foreignField: 'question_id',
        as: 'questionPaper'
      }},
      
      {$match: {
        'questionPaper.entity_id': new this.mongo.ObjectId(req.params.quiz_id)
      }},
      { $project: {
        _id: 0, id: '$_id',
        question: 1, type: 1, options: 1, answer: 1, answers: 1,
        point: 1, mpoint: 1, explanation: 1,
      }}
      
      ]).toArray();
      return {quiz, questions};
    } catch(error) {
      console.error(error);
      return res.status(500).send('Internal server error');
    }
  }
  
}