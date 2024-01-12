const { default: S } = require("fluent-json-schema");

module.exports= {

  schema: {
    response: {
      200: S.array().items(
        S.object()
        .prop('id', S.string())
        .prop('title', S.string())
        .prop('admin', S.string())
        .prop('attempt', S.number())
        .prop('created_at', S.string())
      )
    }
  },
  
  async handler(req, res) {
  try {
    const member_id = new this.mongo.ObjectId(req.user.uuid);
    
    const results = await this.mongo.db.collection('results').aggregate([
    { $lookup: {
      from: 'quizzes',
      localField: 'quiz_id',
      foreignField: '_id',
      as: 'quiz'
    }},
    { $unwind: '$quiz'},
    { $lookup: {
      from: 'users',
      localField: 'quiz.user_id',
      foreignField: '_id',
      as: 'admin'
    }},
    { $unwind: '$admin'},
    { $match: {
      member_id: member_id
    }},
    { $project: {
      _id: 0, id: '$_id', title: '$quiz.title',
      created_at: 1, attempt: 1, admin: {$ifNull: ['$admin.name', '$admin.first_name']},
    }}]).sort({created_at: -1, attempt: -1}).toArray();
    
    return results;
  } catch(error) {
    return res.send(error);
  }
}
}