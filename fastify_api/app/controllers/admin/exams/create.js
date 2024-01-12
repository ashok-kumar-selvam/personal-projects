const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    body: S.object().additionalProperties(false)
      .prop('title', S.string().required())
      .prop('category', S.string().default('general'))
      .prop('description', S.string().default('')),

    response: {
      200: S.object().additionalProperties(false)
        .prop('id', S.string().required())
    }

  },
  
  async handler(req, res) {
    try {
      const {title, description, category = ''} = req.body;
      const exams = this.mongo.db.collection('exams');
      const {ObjectId} = this.mongo;
      const data = {title, description, category,
      created_at: Date.now(),
      user_id: new ObjectId(req.user.admin_id || req.user.uuid ),
      };
      
      const result = await exams.insertOne(data);
      return {id: result.insertedId}
    } catch(error) {
      return res.status(400).send(error.message);
    }
  }
  
}