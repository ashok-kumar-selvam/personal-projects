const { default: S } = require("fluent-json-schema");
const Question = require('../../../helpers/standardObjectSchemas/Question');

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('exam_id', S.string().required()),

    response: {
      200: S.object().additionalProperties(false)
        .prop('exam', S.object().additionalProperties(false)
          .prop('id', S.string())
          .prop('title', S.string())
          .prop('category', S.string())
          .prop('description', S.string())
          .prop('created_at', S.mixed(['number', 'string']))
          )
        .prop('details', S.object().additionalProperties(false)
          .prop('category', S.string())
          .prop('questions', S.number())
          .prop('points', S.number())
          .prop('assignments', S.number())
          .prop('results', S.number())
          .prop('created_at', S.number())
        )
        .prop('questions', S.array().items(
          S.object().additionalProperties(false)
          .prop('id', S.string())
          .extend(Question)
        ))
        .prop('assignments', S.array().items(
          S.object().additionalProperties(false)
          .prop('name', S.string())
          .prop('assign_to', S.string())
          .prop('id', S.string())
          .prop('start', S.number())
          .prop('end', S.number())
          .prop('created_at', S.string())
          .prop('status', S.string())
        ))
    }
  },

  async handler(req, res) {
    try {
      const exams = this.mongo.db.collection('exams');
      const {ObjectId} = this.mongo;
      
      const {exam_id} = req.params;
      const exam = await this.mongo.db.collection('exams').findOne({
        _id: new ObjectId(exam_id)
      }, { projection: {
        _id: 0, id: '$_id',
        title: 1,
        category: 1,
        description: 1,
        created_at: 1,
      }});
      
      const questions = await this.mongo.db.collection('questions').aggregate([
      {$lookup: {
        from: 'questionPaper',
        localField: '_id',
        foreignField: 'question_id',
        as: 'questionPaper'
      }},
      
      {$match: {
        'questionPaper.entity_id': new ObjectId(exam_id)
      }},
      { $project: {
        _id: 0, id: '$_id',
        question: 1, type: 1, options: 1, answer: 1, answers: 1,
        point: 1, mpoint: 1, explanation: 1,
      }}
      
      ]).toArray();
      
      const assignments = await this.mongo.db.collection('assignments').aggregate([
      { $lookup: {
        from: 'users',
        localField: 'assignee.assignee_id',
        foreignField: '_id',
        as: 'users'
      }},
      
      { $lookup: {
        from: 'groups',
        localField: 'assignee.assignee_id',
        foreignField: '_id',
        as: 'groups'
      }},
      
      {$match: {
        exam_id: new ObjectId(exam_id)
      }},
      
      { $project: {
        assign_to: {
          $cond: {
            if: {$eq: ['$assignee.assign_to', 'singleMember']},
            then: {$arrayElemAt: ['$users.first_name', 0]},
            else: {
              $cond: {
              if: {$eq: ['$assignee.assign_to', 'singleGroup']},
              then: {$arrayElemAt: ['$groups.name', 0]},
              else: '$assignee.assign_to'}
            }
          }
        },
        _id: 0, id: '$_id', start: '$time.start', end: '$time.end', created_at: 1, status: 1, name: '$introduction.name',
      }}
      ]).sort({start: -1}).toArray();
      
      const details = {
        created_at: exam.created_at,
        questions: questions.length,
        points: questions.reduce((point, current)  => point+current.point, 0),
        category: exam.category,
        assignments: assignments.length,
        results: await this.mongo.db.collection('results').countDocuments({exam_id: new ObjectId(exam_id)}),
      };
      
      return {exam, questions, assignments, details};
    } catch(error) {
      return res.status(400).send(error.message);
    }
  }
}