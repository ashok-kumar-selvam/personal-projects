const S = require('fluent-json-schema');

module.exports = {

  schema: {

    params: S.object().prop('quiz_id', S.string().required()),
    
    response: {
      200: S.array().items(
        S.object()
        .prop('id', S.string().required())
        .prop('member_type', S.string())
        .prop('name', S.string())
        .prop('attempt', S.number())
        .prop('created_at', S.number())
      )
    }
  },
  
  async handler(req, res) {
    try {
      const quiz_id = new this.mongo.ObjectId(req.params.quiz_id);
      const Results = this.mongo.db.collection('results');
      const results = await Results.aggregate([
        { $lookup: {
          from: 'users',
          localField: 'member_id',
          foreignField: '_id',
          as: 'user'
        }},
        { $project: {
          _id: 0, id: '$_id', member_type: 1, attempt: 1, created_at: 1,
          name: '$user.name'
        }}
      ]).toArray();
  
      return res.send(results);
    } catch(error) {
      console.log(error);
      return res.status(500).send('server error');
    }
  }
}