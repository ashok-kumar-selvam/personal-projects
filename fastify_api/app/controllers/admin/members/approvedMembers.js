const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    response: {
      200: S.array().items(
        S.object()
        .prop('name', S.string())
        .prop('id', S.string())
      )
    }
  },
  
  async handler(req, res) {
    try {
      const user_id = new this.mongo.ObjectId(req.user.admin_id || req.user.uuid);
      const members = await this.mongo.db.collection('users').aggregate([
      { $lookup: {
        from: 'userMembers',
        localField: '_id',
        foreignField: 'member_id',
        as: 'member'
        
      }},
      
      { $match: {
        'member.user_id': user_id,
        'member.status': 'approved'
      }},
      
      { $project: {
        id: '$_id', name: {$concat: ['$first_name', ' ', '$last_name']}
      }}
      ]).toArray();
      return members;
    } catch(error) {
      return res.send(error);
    }
  }
  
}