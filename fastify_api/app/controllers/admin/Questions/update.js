const { default: S } = require("fluent-json-schema");
const Question = require("../../../helpers/standardObjectSchemas/Question");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false)
      .prop('question_id', S.string().required()),

    body: S.object().additionalProperties(false).extend(Question),
    response: {
      200: S.string().const('success'),
    }
  },
  
  async handler(req, res) {
    try {
      let data = req.body;
      let _id = new this.mongo.ObjectId(req.params.question_id);
      delete data['type'];
      
      const result = await this.mongo.db.collection('questions').updateOne({_id}, {$set: data});
      
      if(result.modifiedCount != 1)
        return res.status(400).send(`Unable to update the question. ${modifiedCount} rows were updated. `);
      
      return 'success';
    } catch(error) {
      console.error(error);
      return res.status(500).send('Internal server error. ');
    }
  }
}