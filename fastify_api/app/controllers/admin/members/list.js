const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    response: {
      200: S.object()
        .prop('stats', 
          S.object()
          .prop('total', S.number())
          .prop('approved', S.number())
          .prop('pending', S.number())
      )
      .prop('members', S.array().items(
        S.object()
        .prop('first_name', S.string())
        .prop('last_name', S.string())
        .prop('name', S.string())
        .prop('email', S.string())
        .prop('member_id', S.string())
        .prop('created_at', S.string())
      ))
    }
  },
  
  async handler(req, res) {
    try {
      const members = await this.mongo.db.collection('users').aggregate([
      { $lookup: {
        from: 'userMembers',
        localField: '_id',
        foreignField: 'member_id',
        as: 'member'
      }},
      
      { $match: {
        'member.user_id': new this.mongo.ObjectId(req.user.admin_id || req.user.uuid),
        
      }},
      { $project: {
        first_name: 1, last_name: 1,   email: 1, status: '$member.status', member_id: '$member.member_id', 
        created_at: '$member.created_at', name: {$concat: ['$first_name', ' ', '$last_name']}
      }}
      ]).toArray();
      
      
      const stats = {
        total: members.length,
        approved: members.reduce((number, member) => member.status == 'approved' ? number+1: number, 0),
        pending: members.reduce((number, member) => member.status == 'pending' ? number+1: number, 0),
      };
      return {members, stats};
    } catch(error) {
      return res.send(error);
    }
  }
}