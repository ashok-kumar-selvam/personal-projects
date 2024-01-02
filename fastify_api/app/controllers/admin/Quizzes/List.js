const S = require('fluent-json-schema');

module.exports = {

  schema: {
    response: {
      200: S.array().items(
        S.object()
        .prop('id', S.string().required())
        .prop('title', S.string().required())
        .prop('category', S.string())
        .prop('notes', S.string())
        .prop('questions', S.number())
        .prop('created_at', S.number())
      )
    }
  },

  async handler(req, res) {
    try {
      const quizzes = await this.mongo.db.collection('quizzes').aggregate([
      {$match: {user_id: new this.mongo.ObjectId(req.user.admin_id || req.user.uuid)}},
        { $lookup: {
          from: 'questionPaper',
          localField: '_id',
          foreignField: 'entity_id',
          as: 'questionPaper',
        }},
        { $addFields: {
          questions: {$size: '$questionPaper'}
        }},
        { $project: {
          _id: 0, id: '$_id', title: 1, category: 1, notes: 1,
          created_at: 1, questions: 1
        }}]).sort({created_at: -1}).toArray();
        
      return quizzes;
    } catch(error) {
      console.error(error);
      return res.status(500).send('Internal server error.');
    }
  }
}