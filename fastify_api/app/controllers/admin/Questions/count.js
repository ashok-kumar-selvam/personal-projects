const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    query: S.object().additionalProperties(false).prop('entity_id', S.string().required()),
    Response: {
      200: S.object().additionalProperties(false).prop('count', S.number().minimum(0).required()),
    }
  },

  async handler(req, res) {
    try {
      const questionPaper = this.mongo.db.collection('questionPaper');
      const count = await questionPaper.countDocuments({entity_id: new this.mongo.ObjectId(req.query.entity_id)});
      return {count};
    } catch(error) {
      return res.status(400).send(error.messsage);
    }
  }
  
}