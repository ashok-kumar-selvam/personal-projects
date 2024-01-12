const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('exam_id', S.string().required()),
    body: S.object().additionalProperties(false)
      .prop('title', S.string())
      .prop('category', S.string())
      .prop('description', S.string()),

      response: {
        200: S.object().additionalProperties(false).prop('id', S.string())
      }
  },

  async handler(req, res) {
    try {
      let data = req.body;
      const _id = new this.mongo.ObjectId(req.params.exam_id);
      delete data['id'];
      const result = await this.mongo.db.collection('exams').updateOne({_id}, {$set: data});
      
      if(result?.modifiedCount != 1)
        return res.status(400).send(`Error occured while updating. ${result.modifiedCount} updates occured.`);
      return {id: req.params.exam_id};
    } catch(error) {
      return res.status(400).send(error.message);
    }
  }
  
}