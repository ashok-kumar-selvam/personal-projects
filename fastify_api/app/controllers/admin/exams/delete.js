const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('exam_id', S.string().required()),
    response: {
      200: S.string().const('success')
    }
  },
  
  async handler(req, res) {
    try {
      const _id = new this.mongo.ObjectId(req.params.exam_id);
      const result1 = await this.mongo.db.collection('exams').deleteOne({_id});
      const result2 = await  this.mongo.db.collection('questionPaper').deleteMany({entity_id: _id});
      return 'success';
      
    } catch(error) {
      return res.status(400).send(error.message);
    }
  }
}