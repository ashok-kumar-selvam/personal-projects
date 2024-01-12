/**  The request to /admin/members/:id will be handled by this function
 * the request must contain the property "status" with the value either "approved" or "suspended".
 */

const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('member_id', S.string().required()),
    body: S.object().additionalProperties(false)
    .prop('status', S.string().enum(['approved', 'suspended']).required()),

    response: {
      200: S.string().const('success')
    }
  },
  
  async handler(req, res) {
    try {
      const member_id = new this.mongo.ObjectId(req.params.member_id);
      const user_id = new this.mongo.ObjectId(req.user.admin_id || req.user.uuid);
      const {status} = req.body;
      
      const result = await this.mongo.db.collection('userMembers').updateOne({member_id, user_id}, {$set: { status}});
      if(result.modifiedCount != 1)
        return res.status(400).send(`Unable to update the member details.`);
      return 'success';
    } catch(error) {
      return res.send(error);
    }
  }
  
}