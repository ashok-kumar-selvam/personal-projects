const S = require('fluent-json-schema');

module.exports = {
  schema: {
    //consumes: ['multipart/form-data'],
    body: S.object()
    .additionalProperties(false)
    .prop('title', S.string().required())
    .prop('category', S.string().default('General'))
    .prop('notes', S.string().default(""))
    .prop('expiry', S.string().enum(['never', 'onDate']).default('never'))
    .prop('expires_on', S.string().format(S.FORMATS.DATE))
    .prop('member_only', S.string().enum(['yes', 'no']).default('no'))
    .prop('publish', S.string().const('no').default('no'))
    
    .ifThen(
    S.object().prop('expiry', S.string().const('onDate')),
    S.object().prop('expires_on', S.string().format(S.FORMATS.DATE).required())
    ),

    response: {
      200: S.object().prop('id', S.string())
    }
  },
  
  async handler(req, res) {
    try {
      let data = req.body;
      
      data.user_id = new this.mongo.ObjectId(req.user.admin_id || req.user.uuid);
      data.created_at = Date.now();
      const result = await this.mongo.db.collection('quizzes').insertOne(data);
      
      return {id: result.insertedId};
    } catch(error) {
      return res.send(error);
    }
  }
}