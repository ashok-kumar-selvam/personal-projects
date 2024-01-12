const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('group_id', S.string().required()),
    body: S.object().additionalProperties(false)
      .prop('name', S.string())
      .prop('description', S.string()),

    response: {
      200: S.object().additionalProperties(false).prop('id', S.string())
    }
  },
  async handler(req, res) {
    try {
      const result = await this.mongo.db.collection('groups').updateOne(
      {_id: new this.mongo.ObjectId(req.params.group_id)},
      {$set: req.body});
      if(result.modifiedCount != 1)
        return res.status(400).send(`Unable to update the group details.`);
      return {id: req.params.group_id};
    } catch(error) {
      return res.send(error);
    }
  }
}