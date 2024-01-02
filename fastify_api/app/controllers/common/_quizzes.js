const {correction, getStats, mergeArrays} = require('../../helpers/QuizHelper');
const {getUser} = require('../../helpers/UserHelper');

exports.show = async function(req, res) {
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
      _id: 0, question_id: '$_id', question: 1, options: 1, type: 1, question: 1,
    }}]).toArray();
    
    if(questions.length <= 0)
      return res.status(400).send({action: 'reject', message: 'There is no questions in the quiz.'});
    
    
    const admin = await this.mongo.db.collection('users').findOne({_id: new this.mongo.ObjectId(quiz.user_id)});
    
    return {quiz: {
      id: quiz._id,
      owner: `${admin.first_name} ${admin.last_name}`,
      title: quiz.title,
      category: quiz.category,
      notes: quiz.notes,
      created_at: quiz.created_at,
    
    }, questions};
  } catch(error) {
    return res.send(error);
  }
}

exports.save = async function(req, res) {
  try {
    let data = req.body;
    const user = await getUser(req);
    
    const quiz_id = new this.mongo.ObjectId(data.quiz_id);
    const member_id = new this.mongo.ObjectId(user.uuid);
    
    const questions = await this.mongo.db.collection('questions').aggregate([
    { $lookup: {
      from: 'questionPaper',
      localField: '_id',
      foreignField: 'question_id',
      as: 'questionPaper'
    }},
    { $match: {
      'questionPaper.entity_id': new this.mongo.ObjectId(data.quiz_id)
    }}, 
    { $project: {
      _id: 0, question_id: '$_id', options: 1, type: 1, question: 1,
      correctAnswer: {$ifNull: ['$answers', '$answer']}
    }}]).toArray();
    
    if(questions.length != data.questions.length)
      return res.status(400).send('The questions has been modified during the exam from the admin side. Unable to do the correction.');
    
    const resultQuestions = mergeArrays(questions, data.questions);
    const correctedQuestions = resultQuestions.map(correction);
    let stats = getStats(correctedQuestions);
    stats.taken_time = data.time;
    const result = {
      type: 'quiz',
      quiz_id: quiz_id,
      member_id: member_id,
      member_type: user.type,
      attempt: await this.mongo.db.collection('results').countDocuments({quiz_id, member_id})+1,
      stats: stats,
      questions: correctedQuestions,
      created_at: Date.now(),
    };
    const insert  = await this.mongo.db.collection('results').insertOne(result);
    
    return {id: insert.insertedId};
    
    
  } catch(error) {
    return res.send(error);
    
  }
}

exports.result = async function(req, res) {
  try {
    const result_id = new this.mongo.ObjectId(req.params.result_id);
    const user = await getUser(req);
    
    let result = await this.mongo.db.collection('results').findOne({_id: result_id});
    if(!result)
      return res.status(400).send('Unable to find the result.');
    
    const member = await this.mongo.db.collection('users').findOne({_id: new this.mongo.ObjectId(result.member_id)}, { projection: {
      name: {$concat: ['$first_name', ' ', '$last_name']},
    }});
    const quiz = await this.mongo.db.collection('quizzes').findOne({_id: new this.mongo.ObjectId(result.quiz_id)}, { projection: {
      title: 1, category: 1, notes: 1, }});
    
    return {
      quiz, member, result
    };
  } catch(error) {
    return res.send(error);
    
  }
}