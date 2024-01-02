class QuestionTypeError extends Error {
  constructor(message) {
    super(message);
    this.name = 'QuestionTypeError';
  }
}
exports.QuestionTypeError = QuestionTypeError;
exports.type1 = (row, question) => {
  let answerCount = row.slice(6).reduce((count, value) => (value.startsWith('*') ? count+1: count), 0);
  if(answerCount != 1)
    throw new QuestionTypeError(`Question ${question.number} must contain exactly one answer.`);

  question.options = row.slice(6).map(item => (item.startsWith('*') ? item.slice(1): item));
  question.answer = row.slice(6).find(item => item.startsWith('*')).slice(1);
}

exports.type2 = (row,  question) => {
  let answer = row[6];
  if(answer !== true && answer !== false)
    throw new QuestionTypeError(`Question ${question.number}: The answer should be either true or false. `);
  question.options = [answer, !answer];
  question.answer = answer;
}

exports.type3 = (row, question) => {
  let answerCount = row.slice(6).reduce((count, value) => (value.startsWith('*') ? count+1: count), 0);
  if(answerCount < 1)
    throw new QuestionTypeError(`Please provide answer in question ${question.number}`);

  question.options = row.slice(6).map(item => (item.startsWith('*') ? item.slice(1).trim(): item.trim()))
  question.answers = row.slice(6).filter(item => item.startsWith('*')).map(item => item.slice(1).trim())
}

