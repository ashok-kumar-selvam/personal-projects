const S = require('fluent-json-schema');

module.exports = {

  schema: {

    params: S.object().prop('quiz_id', S.string().required()),

    response: {
      200: S.string()
    }
  },

  async handler(req, res) {
    try {
      const quiz_id = new this.mongo.ObjectId(req.params.quiz_id);
      const result1= await this.mongo.db.collection('quizzes').deleteOne({_id: quiz_id});
      const result2 = await this.mongo.db.collection('questionPaper').deleteMany({entity_id: quiz_id});
      return res.send('success');
      
    } catch(error) {
      return res.send(error);
    }
  }
}