const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    response: {
      200: S.object().prop('quizzes', S.array().items(
        S.object()
        .prop('id', S.string())
        .prop('title', S.string())
        .prop('category', S.string())
        .prop('admin', S.string())
        .prop('created_at', S.string())
        .prop('expiresOn', S.string())
      ))
    }
  },
  
  async handler(req, res) {
  try {
    const member_id = new this.mongo.ObjectId(req.user.uuid);
    const quizzes = await this.mongo.db.collection('quizzes').aggregate([
    { $lookup: {
      from: 'userMembers',
      localField: 'user_id',
      foreignField: 'user_id',
      as: 'userMembers'
      
    }},
    { $lookup: {
      from: 'users',
      localField: 'user_id',
      foreignField: '_id',
      as: 'user'
    }},
    { $match: {
      'userMembers.member_id': member_id,
      'userMembers.status': 'approved',
      publish: 'yes',
    }},
    { $project: {
      admin: {$arrayElemAt: ['$user.first_name', 0]},
      title: 1, _id: 0, id: '$_id', category: 1, created_at: 1, expiresOn: 1,
      
  }}]).toArray();
    
    
    return {quizzes};
  } catch(error) {
    return res.send(error);
  }
}
}