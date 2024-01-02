const S = require('fluent-json-schema');
const {getUser} = require('../../../helpers/UserHelper');

module.exports = {

  schema: {
    params: S.object().prop('quiz_id', S.string()),

    response: {
      200: S.object()
      .prop('quiz', S.object()
        .prop('id', S.string())
        .prop('title', S.string())
        .prop('owner', S.string())
        .prop('category', S.string())
        .prop('notes', S.string())
        .prop('created_at', S.number())
      )
      .prop('questions', S.array().items(
        S.object()
        .prop('question_id', S.string())
        .prop('question', S.string())
        .prop('options', S.array())
        .prop('type', S.string())
      ))
    }
  },

  async handler(req, res) {
    try {
      const user = await getUser(req);
      const quiz_id = new this.mongo.ObjectId(req.params.quiz_id);
      const quiz = await this.mongo.db.collection('quizzes').findOne({_id: quiz_id});
      if(!quiz)
        return res.status(404).send({message: 'Unable to find the quiz.'});
      
      if(quiz.publish != 'yes')
        return res.status(404).send({message: 'Unable to find the quiz.'});
      
      if(quiz.member_only == 'yes') {
        if(!user)
          return res.status(400).send({action: 'login'});
        
        if(user.type != 'member')
          return res.status(400).send({action: 'reject', message: 'The quiz is only available for particular members.'});
        
        const userMember = await this.mongo.db.collection('userMembers').findOne({
          user_id: new this.mongo.ObjectId(quiz.user_id),
          member_id: new this.mongo.ObjectId(user.uuid),
          status: 'approved'
        });
        
        if(!userMember)
          return res.status(400).send({action: 'reject', message: 'This quiz is only available for approved members.'});
        
      } else if(quiz.member_only == 'no') {
        if(!user)
          return res.status(400).send({action: 'register', message: "please register to continue"});
        
        const types = ['member', 'admin', 'anonymous'];
        if(!types.includes(user.type))
          return res.status(400).send({action: 'reject', messsage: 'You are not allowed to access this quiz.'});
        
        
        
      }
      
      
      if(quiz.expires_on && quiz.expires_on > 0 && quiz.expires_on < Math.floor(Date.now()/1000))
        return res.status(400).send({action: 'reject', message: 'The quiz has expired.'});
      
      
      
      const questions = await this.mongo.db.collection('questions').aggregate([
      { $lookup: {
        from: 'questionPaper',
        localField: '_id',
        foreignField: 'question_id',
        as: 'questionPaper'
      }},
      { $match: {
        'questionPaper.entity_id': quiz_id
      }},
      { $project: {
        _id: 0, question_id: '$_id', question: 1, options: 1, type: 1, 
      }}]).toArray();
      
      if(questions.length <= 0)
        return res.status(400).send({action: 'reject', message: 'There is no questions in the quiz.'});
      
      
      const admin = await this.mongo.db.collection('users').findOne({_id: new this.mongo.ObjectId(quiz.user_id)});
      
      return {quiz: {
        id: quiz._id,
        owner: `${admin.name || admin.first_name} `,
        title: quiz.title,
        category: quiz.category,
        notes: quiz.notes,
        created_at: quiz.created_at,
      
      }, questions};
    } catch(error) {
      return res.send(error);
    }
  }
}