const { default: S } = require("fluent-json-schema");
const Question = require("../../../helpers/standardObjectSchemas/Question");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false)
      .prop('entity_id', S.string().required()),

      response: {
        200: Question.only(['question', 'type', 'point', 'mpoint'])
        .prop('id', S.string())
        .prop('title', S.string())
        .prop('created_at', S.string())
      }
  },
  
  async handler(req, res) {
    try {
      const entity_id = new this.mongo.ObjectId(req.params.entity_id);
      const user_id = new this.mongo.ObjectId(req.user.admin_id || req.user.uuid);
      
      const questions = await this.mongo.db.collection('questions').aggregate([
      { $lookup: {
        from: 'questionPaper',
        localField: '_id',
        foreignField: 'question_id',
        as: 'questionPaper'
      }},
      { $match: {
        user_id: user_id,
        'questionPaper.entity_id': {$ne: entity_id}
      }},
      
      
      { $lookup: {
        from: 'exams',
        localField: 'source_id',
        foreignField: '_id',
        as: 'exams',
        
      }},
      { $lookup: {
        from: 'quizzes',
        localField: 'source_id',
        foreignField: '_id',
        as: 'quizzes'
      }},
      
      
      { $addFields: {
        title: {
          $cond: [
          {$eq: [{$size: '$exams'}, 0]}, 
            {
              $cond: [
              {$eq: [{$size: '$quizzes'}, 0]},
            'deleted source',
            {$arrayElemAt: ['$quizzes.title', 0]}
            ],
            },
        {$arrayElemAt: ['$exams.title', 0]}
        ]}
      }},
      
      { $project: {
        _id: 0, id: '$_id', title: 1, question: 1,type: 1,
        point: 1, mpoint: 1, created_at: 1,
      }}
      ]).sort({created_at: -1}).toArray();
      
      return questions;
    } catch(error) {
      return res.send(error);
    }
  }
}