const { default: S } = require("fluent-json-schema");

module.exports = {
  schema: {
    params: S.object().additionalProperties(false)
    .prop('attempt_id', S.string().required())
  },

  async handler(req, res) {
    try {
      const {db, ObjectId} = this.mongo;
      const attempt_id = new ObjectId(req.params.attempt_id);
      const attempt = await db.collection('attempts').findOne({_id: attempt_id});
      if(!attempt)
        return res.status(404).send('Unable to find the result');

      const answers = await db.collection('answers').aggregate([
        { $lookup: {
          from: 'questions',
          localField: 'question_id',
          foreignField: '_id',
          as: 'originalQuestion'
        }},
        { $unwind: '$originalQuestion'},
        { $match: {attempt_id}},
        { $project: {
          _id: 0, id: '$_id', question: 1, type: 1, options: 1, givenAnswer: '$answer', correctAnswer: {$ifNull: ['$originalQuestion.answers', '$originalQuestion.answer']}, point: 1, explanation: '$originalQuestion.explanation',
        }}
      ]).toArray();
      
    }
  }
}