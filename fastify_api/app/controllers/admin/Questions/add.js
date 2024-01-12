const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false)
      .prop('entity_id', S.string().required()),

      body: S.array().items(S.string()).minItems(1),
      response: {
        200: S.string().const('success')
      }
  },

  async handler(req, res) {
    try {
      const entity_id = new this.mongo.ObjectId(req.params.entity_id);
      const questions = req.body.map(id => new this.mongo.ObjectId(id));
      const userId = new this.mongo.ObjectId(req.user.admin_id || req.user.uuid);
      
      const result1 = await this.mongo.db.collection('questions').aggregate([
      { $match: {
        _id: {$in: questions},
        user_id: userId
      }},
      {$count: 'count'}
      ]).toArray();
      
      const totalCount = result1.length > 0 ? result1[0].count: 0;
      
      if(totalCount != questions.length)
        return res.status(400).send(`Invalid request`);
      const data = questions.map(question_id => ({entity_id, question_id}));
      const result = await this.mongo.db.collection('questionPaper').insertMany(data);
      
      if(result.insertedCount != data.length)
        return res.status(400).send('Unexpected error. Not all of the questions were added. ');  
      return 'success';
    } catch(error) {
      return res.send(error);
    }
  }
}