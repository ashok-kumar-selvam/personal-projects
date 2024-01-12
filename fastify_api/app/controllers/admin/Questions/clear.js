const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('entity_id', S.string().required()),
    response: {
      200: S.string().const('success'),
    }
  },
  
  async handler(req, res) {
    try {
      const entity_id = new this.mongo.ObjectId(req.params.entity_id);
      const result = await this.mongo.db.collection('questionPaper').deleteMany({entity_id});
      
      if(result.deletedCount <= 0)
        return res.status(400).send(`Unable to delete any question.`);
      return 'success';
    } catch(error) {
      return res.send(error);
    }
  }
  
}