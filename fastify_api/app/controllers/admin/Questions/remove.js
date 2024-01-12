const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    body: S.object().additionalProperties(false)
    .prop('entity_id', S.string().required())
    .prop('question_id', S.string().required()),

    response: {
      200: S.string().const('success'),
    }
  },
  
  async handler(req, res) {
    try {
      const entity_id = new this.mongo.ObjectId(req.body.entity_id);
      const question_id = new this.mongo.ObjectId(req.body.question_id);
      
      const question = await this.mongo.db.collection('questions').findOne({_id: question_id});
      
      if(!question)
        return res.status(404).send('Unable to find the question.');
      
      const  entity = await this.mongo.db.collection('exams').findOne({_id: entity_id})
      || await this.mongo.db.collection('quizzes').findOne({_id: entity_id});
      
      if(!entity)
        return res.status(404).send('The entity is not found.');
      
      if(entity.user_id != req.user.uuid && entity.user_id != req.user.admin_id)
        return res.status(403).send('The ownership can not be approved.');
      
      const result = await this.mongo.db.collection('questionPaper').deleteOne({entity_id, question_id});
      if(result.deletedCount != 1)
        return res.status(400).send('Invalid request');
      return 'success';
    } catch(error) {
      return res.status(500).send(error.message);
    }
  }
}