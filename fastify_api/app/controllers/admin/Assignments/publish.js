const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('assign_id', S.string().required()),
    body: S.object().additionalProperties(false).prop('published', S.string().enum(['yes', 'no']).required()),
    response: {
      200: S.string().const('success')
    }
  },
  
  async handler(req, res) {
    try {
      const _id = new this.mongo.ObjectId(req.params.assign_id);
      const {body} = req;
      const assignment = await this.mongo.db.collection('assignments').findOne({_id});
      const obj = {...assignment['result'], published: body.published};
      const result = await this.mongo.db.collection('assignments').updateOne({_id}, {$set: {result: obj}});
      return 'success';
      
    } catch(error) {
      return res.send(error);
    }
  }
}