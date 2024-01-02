const S = require('fluent-json-schema');

module.exports = {
  /**
   * Required Body Params
   */
  schema: {
    body: S.object()
    .additionalProperties(false)
    .prop('testfield', S.string().required()),
    response: {
      200: S.object()
      .additionalProperties(false)
      .prop('users', S.array().items(S.object().prop('_id', S.string().required()))),
      

      }
    },
    async handler(req, res) {
      const Users = this.mongo.db.collection('users');
      const users = await Users.find().toArray();
      return res.status(400).send('some error');
      return {users};
    }
  }
