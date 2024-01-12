const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    response: {
      200: S.array().items(
        S.object()
        .prop('id', S.string())
        .prop('name', S.string())
        .prop('email', S.string())
        .prop('userId', S.string())
        .prop('created_at', S.string())
      )
    }
  },
  
  async handler(req, res) {
    try {
      const instructors =  await this.mongo.db.collection('users').aggregate([
      { $match: {admin_id: new this.mongo.ObjectId(req.user.uuid), type: 'instructor'}},
        { $project: {
          _id: 0, id: '$_id', name: 1, created_at: 1, email: 1, userId: 1
        }},
        
      ]).toArray();
      return instructors;
    } catch(error) {
      return res.send(error);
    }
  }
  
}