const S = require('fluent-json-schema');

module.exports = {

  schema: {

    params: S.object().prop('quiz_id', S.string().required()),

    body: S.object()
    .additionalProperties(false)
    .prop('title', S.string())
    .prop('category', S.string())
    .prop('notes', S.string())
    .prop('expiry', S.string().enum(['never', 'onDate']))
    .prop('expires_on', S.string().format(S.FORMATS.DATE))
    .prop('member_only', S.string().enum(['yes', 'no']))
    .prop('publish', S.string().enum(['yes', 'no'])),

    response: {
      200: S.object().prop('id', S.string())
    }
  },

  async handler(req, res) {
    try {
      const data = req.body;
      const _id = new this.mongo.ObjectId(req.params.quiz_id);
      const result = await this.mongo.db.collection('quizzes').updateOne({_id}, {$set: data});
      
      if(result.modifiedCount != 1)
        return res.status(400).send('Invalid request. Unable to update. ');
      return {id: _id};
    } catch(error) {
      return res.send(error);
    }
  }
}