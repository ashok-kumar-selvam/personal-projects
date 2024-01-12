const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    body: S.object().additionalProperties(false)
      .prop('name', S.string().required())
      .prop('description', S.string().required()),
      response: {
        200: S.object().additionalProperties(false).prop('id', S.string())
      }
  },
  
  async handler(req, res) {
    try {
      const data = {
        user_id: new this.mongo.ObjectId(req.user.admin_id || req.user.uuid),
        created_at: Date.now(),
        members: [],
        ...req.body
      };
      const result = await this.mongo.db.collection('groups').insertOne(data);
      return {id: result.insertedId};
    } catch(error) {
      return res.send(error);
    }
  }
}