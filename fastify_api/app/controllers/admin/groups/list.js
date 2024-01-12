const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    response: {
      200: S.object().additionalProperties(false)
        .prop('groups', S.array().items(
          S.object()
          .prop('id', S.string())
          .prop('name', S.string())
          .prop('description', S.string())
          .prop('total', S.number())
        ))
    }
  },
  
  async handler(req, res) {
    try {
      const groups = await this.mongo.db.collection('groups').aggregate([
      
      { $match: {
        user_id: new this.mongo.ObjectId(req.user.admin_id || req.user.uuid)
      }},
      { $sort: {
        created_at: -1
      }},
      { $project: {
        _id: 0, id: '$_id', name: 1, description: 1,
        created_at: 1,
        total: {$cond: {
          if: {$isArray: "$members"},
          then: {$size: '$members'},
        else: 0}},
      }}]).toArray();
      return {groups};
    } catch(error) {
      return res.send(error);
    }
  }
}