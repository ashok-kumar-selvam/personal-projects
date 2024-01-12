const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('instructor_id', S.string().required()),
    response: {
      200: S.string().const('success')
    }
  },
  
  async handler(req, res) {
    try {
      const result = await this.mongo.db.collection('users').deleteOne({_id: new this.mongo.ObjectId(req.params.instructor_id), type: 'instructor'});
      
      if(result.deletedCount != 1)
        return res.status(400).send(`Unable to delete.`);
      
      return 'success';
    } catch(error) {
      return res.send(error);
    }
  }
}