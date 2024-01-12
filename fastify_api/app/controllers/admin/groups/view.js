const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('group_id', S.string().required()),
    response: {
      200: S.object().additionalProperties(false)
        .prop('group', S.object()
          .prop('id', S.string())
          .prop('name', S.string())
          .prop('description', S.string())
        )
        .prop('members', S.array().items(
          S.object()
          .prop('name', S.string())
          .prop('id', S.string())
          .prop('email', S.string())
        ))
    }
    },

  async handler(req, res) {
    try {
      const group_id = new this.mongo.ObjectId(req.params.group_id);
      const group = await this.mongo.db.collection('groups').findOne({_id: group_id}, {
        projection: {
          _id: 0, id: '$_id', name: 1, description: 1, members: 1
        }
      });
      const members = await this.mongo.db.collection('users').aggregate([
      { $lookup: {
        from: 'userMembers',
        localField: '_id',
        foreignField: 'member_id',
        as: 'userMembers'
      }},
      { $match: {
        _id: {$in: group.members},
        'userMembers.status': 'approved',
        'userMembers.user_id': new this.mongo.ObjectId(req.user.admin_id || req.user.uuid),
      }},
      
      { $project: {
        _id: 0, id: '$_id', email: 1,
        name: {$concat: ['$first_name', ' ', '$last_name']},
      }},
      ]).toArray();
      return {group, members};
    } catch(error) {
      return res.send(error);
    }
  }
  
}