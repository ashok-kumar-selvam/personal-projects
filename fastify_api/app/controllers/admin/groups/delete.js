const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('group_id', S.string().required()),
    response: {
      200: S.string().const('success')
    }
  },
  
  async handler(req, res) {
    try {
      const _id = new this.mongo.ObjectId(req.params.group_id);
      
      const result = await this.mongo.db.collection('groups').deleteOne({_id});
      if(result.deletedCount != 1)
        return res.status(400).send(`Unable to delete.`);
      
      
      const result3 = await this.mongo.db.collection('assignments').deleteMany({assignee_id: _id});
      
      return 'success';
    } catch(error) {
      return res.send(error);
    }
  }
}