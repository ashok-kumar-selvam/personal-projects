const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('member_id', S.string().required()),
    response: {
      200: S.string().const('success')
    }
  },
  
  async handler(req, res) {
    try {
      const user_id = new this.mongo.ObjectId(req.user.admin_id || req.user.uuid);
      const member_id = new this.mongo.ObjectId(req.params.member_id);
      
      const groups = await this.mongo.db.collection('groups').updateMany({user_id}, {$pull: {members: member_id}});
      const assignments = this.mongo.db.collection('assignments').deleteMany({user_id, 'assignee.assignee_id': member_id});
      const result = this.mongo.db.collection('userMembers').deleteOne({user_id, member_id});
      return 'success';
      
    } catch(error) {
      return res.send(error);
    }
  }
}