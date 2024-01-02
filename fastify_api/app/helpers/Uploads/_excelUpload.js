const setError = (msg) => {
  return [false, msg];
}

const sanitize = (array) => {
  for(let i = array.length-1; i >= 0; i--) {
    if(array[i] === null) {
      delete array[i];
      continue;
    }
    return array;
  }
  
}

module.exports = async function(file, entityType) {
  if(file.length <= 0)
    return setError('The document seems to be empty. ');
let questionArray = [];  
  for(let  [index, row] of file.entries()) {
    
    // the first row and the row in even numbers should be avoided. because they will contain the 'yes' part
    if(index == 0 || index%2 == 0)
      continue;
    
    //the nulls after the 'yes' value, should be removed
    row = sanitize(row);
    
    // after sanitize, we have to check whether the array has any items.
    if(row.length == 0)
      continue;
    
    if(row.length < 6)
      return setError(`The question ${(index+1)/2} seems to have some issue. Please check and try again.  `);
    
    // extract the required values from the row.
    const [number = ((index+1)/2), point = 1, mpoint = 0, explanation = '', type, question] = row;
    console.log('the row is ', row);
    // define the allowed types 
    const types = entityType == 'exams' ? ["single_choice", "multi_choice", "true_or_false", 
    "match_it", "fill_the_blanks", "descriptive",
    "error_correction", "cloze"]:
    entityType == 'quizzes' ? ['single_choice', 'multi_choice', 'true_or_false']: [];
    
    if(!type)
      return setError(`The question type field is required in question  ${number}`);
    
    if(!types.includes(type))
      return setError(`Question ${number}: The ${type} type is not supported in ${entityType}`);
    
    if(!question)
      return setError(`The question can not be empty in question ${number}`);
    let row2 = sanitize(file[index+1] ?? []);
let questionObject     = {type, point, mpoint, question, explanation};
let options, answers, answer, answerCount, answerIndex;
    switch(type) {
      case "single_choice":
      console.log('the single choice is running');
      if(row.length < 8)
        return setError(`The Single choice should have at least 2 options. Please check question ${number}`);
      
      if(row2.length < 7)
        return setError(`In question ${number}, the answer is missing. `);
      
      options = row.slice(6);
      answers = row2.slice(6);
      answerIndex = answers.indexOf('yes');
      
      if(answerIndex === -1)
        return setError(`Question: ${number}: Please provide an answer. `);
      
      answer = options[answerIndex];
      if(!answer)
        return setError(`Question ${number}: The answer is empty. `);
      
      answerCount = answers.reduce((count, value) => (value === 'yes' ? count+1: count), 0);
      
      if(answerCount > 1)
        return setError(`Question ${number}: You can only provide one answer to this question. `);
      
      questionObject = {...questionObject, answer, options};
      
      break;
      case "true_or_false":
      if(row.length != 8)
        return setError(`Question ${number}: The question must have 2 options.`)
      
      if(row2.length < 7)
        return setError(`question ${number}: 
        The answer row must have value 'yes' below the options. `);
      
       options = row.slice(6);
       answers = row2.slice(6);
       answerIndex = answers.indexOf('yes');
      if(answerIndex === -1)
        return setError(`Question ${number}: Please provide 'yes' below the correct option.`);
      
      answer = options[answerIndex];
      
      if(answer !== true && answer !== false)
        return setError(`Question ${number}: The answer should be either true or false. ${answer}`);
      
      answerCount = answers.reduce((count, value) => (value === 'yes' ? count+1: count), 0);
      if(answerCount != 1)
        return setError(`Question ${number}: You can only provide one answer.`);
      
      questionObject = {...questionObject, answer, options};
      
      break;
      case "multi_choice":
      if(row.length < 8 || row2.length < 7)
        return setError(`Question ${number}: 
        The question must have at least 2 options and 1 answer. `);
      
      options = row.slice(6);
      
      
      answers = row2.reduce((selected, answer, index) => {
        if(index >= 6 && answer == 'yes')
          selected.push(row[index]);
        return selected;
      }, [])
      
      if(answers.length <= 0)
        return setError(`Question ${number}: There should be at least one answer.`);
      
      if(!answers.every(item => item != null && item != undefined))
        return setError(`Question ${number}: The answer can not be empty. `);
      
      
      questionObject = {...questionObject, options, answer: answers};
      
      break;
      case "fill_the_blanks":
      case "error_correction":
      answer = row.slice(6);
      if(answer.length < 1)
        return setError(`Question ${number}: Please provide at least one answer. `);
      
      if(!answer.every(item => item !== null && item !== undefined))
        return setError(`Question ${number}: Empty values are not accepted as answer. `);
      
      questionObject.answer = answer;
      
      break;
      case "match_it":
      if(row.length < 8 || row2.length < 8)
        return setError(`Question ${number}: Please provide at least 2 pair of question and         answer. `);
      
      let questions = row.slice(6);
      answers = row2.slice(6);
      
      if(questions.length != answers.length)
        return setError(`Question ${number}: The number of Q and A must be same. `);
      
      if(!questions.every(item => item !== null && item !== undefined) || !answers.every(t => t !== null && t !== undefined))
        return setError(`Question ${number}: Empty values are not allowed in Q and A.`);
      
      answer = answers.map((ans, index) => ({answer: ans, question: questions[index]}));
      questionObject.answer = answer;
      
      break;
      case "cloze":
      
      if(row.length < 7)
        return setError(`Question ${number}: Please provide some answers. `);
      
      answer = row.slice(6);
      
      if(!answer.every(t => t !== null && t !== undefined))
        return setError(`Question ${number}: Empty values are not supported in answer. `);
      
      
      
      
      
      const matches = question.match(/-{3,}/g) ?? [];
      
      if(matches.length <= 0) {
        return this.setError(` Please add at least 1 gap using --- `);
      } else if(matches.length < answer.length) {
        return this.setError(` ${answer.length-matches.length} answers should be removed. `);
      } else if(matches.length > answer.length) {
        return this.setError(` ${matches.length-answer.length} answers are needed. `);
} 
      questionObject.answer = answer;
      break;
      case "descriptive":
      break;
      default:
    return setError(`Question ${number}: Unknown question type ${type} `);
    }
 await questionArray.push(questionObject);
 
    
  }
  
  return [true, questionArray];
}