const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('assign_id', S.string().required()),
    body: S.object().additionalProperties(false).prop('status', S.string().enum(['active', 'inactive']).required()),
    response: {
      200: S.string().const('success')
    }
  },
  
  async handler(req, res) {
    try {
      const _id = new this.mongo.ObjectId(req.params.assign_id);
      const {body} = req;
      const result = await this.mongo.db.collection('assignments').updateOne({_id}, {$set: {status: body.status}});
      return 'success';
      
    } catch(error) {
      return res.send(error);
    }
  }
}