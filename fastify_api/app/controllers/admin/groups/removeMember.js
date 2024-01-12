const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('group_id', S.string().required()),
    query: S.object().additionalProperties(false).prop('member_id', S.string().required()),
    response: {
      200: S.string().const('success')
    }
  },
  
  async handler(req, res) {
    try {
      const member_id = new this.mongo.ObjectId(req.query.member_id);
      const group_id = new this.mongo.ObjectId(req.params.group_id);
      const result = await this.mongo.db.collection('groups').updateOne({_id: group_id},
        {$pull: {members: member_id}});
      return 'success';
      
    } catch(error) {
      return res.send(error);
    }
  }
  
}