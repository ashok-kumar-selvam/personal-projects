const UserHelper = require('./UserHelper');
const Base = require('../config/Base');

exports.correction = (question) => {
  try {
    const [TYPE1, TYPE2, TYPE3] = Base.getQuestionTypeNames('type1', 'type2', 'type3');
    const {answer = null, correctAnswer = null} = question;

    if(answer === null || answer === '__None__')
      return false;

    switch(question.type) {
      case TYPE1:
        question.point = answer.trim() === correctAnswer.trim() ? 1: 0;
      break;
      case TYPE2:
        question.point = Boolean(answer) === Boolean(correctAnswer) ? 1: 0;
      break;
      case TYPE3:
        if(!Array.isArray(answer) || !Array.isArray(correctAnswer))
          return false;

        question.point = correctAnswer.every(item => answer.includes(item)) ? 1: 0;
    }

    question.isCorrect = Boolean(question.point);
    return question;
  } catch(error) {
    console.error(error);
    return false;
  }
}

exports.getStats = (questions) => {
  const stats = {
    total_questions: questions.length,
    attempted_questions: 0,
    answered_questions: 0,
    correct_answers: 0,
    total_points: questions.length,
    taken_points: 0,
    
  };
  for(let question of questions) {
    stats['taken_points'] += question.point || 0;
    
    if(question.isCorrect && question.isCorrect == true)
      stats['correct_answers']++;
    
    if(question.attempted && question.attempted == true)
      stats['attempted_questions']++;
    
    if(question.answer != '__None__')
      stats['answered_questions']++;
  }
  return stats;
}

exports.mergeArrays = (array1, array2) => {
  
  const obj1 = array1.reduce((obj, current) => {
    obj[current.question_id] = current;
    return obj;
  }, {});
  const obj2 = array2.reduce((obj, current) => {
    obj[current.question_id] = current;
    return obj;
  }, {});
  
  for(let id in obj1) {
    if(obj2.hasOwnProperty(id))
      Object.assign(obj1[id], obj2[id]);
    
  }
  return Object.values(obj1);
}