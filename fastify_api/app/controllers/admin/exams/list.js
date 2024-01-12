const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    response: {
      200: S.object().additionalProperties(false)
      .prop('exams', S.array().items(
        S.object().additionalProperties(false)
        .prop('id', S.string())
        .prop('title', S.string())
        .prop('category', S.string())
        .prop('description', S.string())
        .prop('created_at', S.mixed(['number', 'string']))
      ))
    }
  },
  
  async handler(req, res) {
    try {
      const {ObjectId} = this.mongo;
      const exams =   await this.mongo.db.collection('exams').find({
        user_id: new ObjectId(req.user.admin_id || req.user.uuid)
      }, {
        projection: {
          _id: 0, id: '$_id',
          title: 1, category: 1, description: 1, created_at: 1
        }
      }).sort({created_at: -1}).toArray();
      return {exams};
    } catch(error) {
      return res.status(400).send(error.message);
    }
  }
}