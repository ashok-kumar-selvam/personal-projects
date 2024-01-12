const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('group_id', S.string().required()),
    body: S.array().items(S.string()).minItems(1),
    response: {
      200: S.string().const('success')
    }
  },
  
  async handler(req, res) {
    try {
      const {body} = req;
      const group_id = new this.mongo.ObjectId(req.params.group_id);
      const members  = body.map(id => new this.mongo.ObjectId(id));
      
      const result = await this.mongo.db.collection('groups').updateOne({_id: group_id},
      {$addToSet: {members: {$each: members}}});
      
      if(result.modifiedCount != members.length)
        return res.status(400).send(`There was some error updating members`);
      
      return 'success';
      
    } catch(error) {
      return res.send(error);
    }
  }
}