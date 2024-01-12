const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('group_id', S.string().required()),
    response: {
      200: S.array().items(
        S.object().additionalProperties(false)
        .prop('id', S.string())
        .prop('name', S.string())
        .prop('email', S.string())
      )
    }
  },
  
  async handler(req, res) {
    try {
      const group_id = new this.mongo.ObjectId(req.params.group_id);
      const group = await this.mongo.db.collection('groups').findOne({_id: group_id});
      
      const nonmembers = await this.mongo.db.collection('users').aggregate([
      { $lookup: {
        from: 'userMembers',
        localField: '_id',
        foreignField: 'member_id',
        as: 'members'
      }},
      { $match: {
        'members.user_id': new this.mongo.ObjectId(req.user.admin_id || req.user.uuid),
        'members.status': 'approved',
        _id: {$nin: group.members},
      }},
      { $project: {
        _id: 0, id: '$_id', email: 1,
        name: {$concat: ['$first_name', ' ', '$last_name']}
      }}]).toArray();
      return nonmembers;
    } catch(error) {
      return res.send(error);
    }
  }
}