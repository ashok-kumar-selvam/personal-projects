const UploadHelper = require('./UploadHelper');
const xlsx = require('xlsx');
const ExcelUploadQuestionTypeHelper = require('./XlsxUploadQuestionTypeHelper');
const Base = require('../../config/Base')

class XlsxUploadHelper extends UploadHelper {


  //this method removes the null and empty values from each column of each row and removes the rows which have only 4 columns.
  sanitize(array) {
    
    return array.map(row => row.filter((column, index) => (index > 3 ? (column && column !== null && column != ' '): true))).filter(row => row.length > 3);
  }

  extract() {
    try {
      if(!this.validateType())
        return false;

      const workbook = xlsx.read(this.file);
      const worksheet = workbook.Sheets['Sheet1'];
      let extracted = xlsx.utils.sheet_to_json(worksheet, {header: 1});
      extracted.shift();
      let sanitised = this.sanitize(extracted);
      let numberOfQuestions = sanitised.length;
      if(numberOfQuestions < 1)
        return this.setError(`We were unable to extract any questions from the file. Please check and try again.`);

      if(numberOfQuestions < this.questionCount)
        return this.setError(`We were able to extract only ${numberOfQuestions} from the file. Please check and try again.`);

      if(numberOfQuestions > this.questionCount)
        return this.setError(`We were able to extract ${numberOfQuestions} questions from the file but you mentioned only ${this.questionCount} questions. Please check and try again. `);

      this.extracted = sanitised;
      return true;
    } catch(error) {
      console.error(error);
      return this.setError("Error occured");
    }
  }

  validateArray() {
    try {
      if(!this.extract())
        return false;

      if(this.extracted.length <= 0)
        return this.setError("The file seems to be empty. Please check and try again.");

        let questions = [];
      for(let [column, row] of  this.extracted.entries()) {
        const [number = column+1, point = 1, mpoint = 0, explanation = "", type, question] = row;

        if(!type)
          return this.setError(`The question type is required in question ${number}`);
        
        if(!this.allowedQuestionTypes.includes(type))
          return this.setError(`The question type ${type} is not allowed in ${this.entityType} in question ${number}`);

        if(!question)
          return this.setError(`Please provide the question for question ${number}. `);
        questions.push({number, point, mpoint, explanation, type, question, row});
      }
      this.questions = questions;
      return true;
    } catch(error) {
      console.error(error);
      return this.setError("Error occured");
    }
  }

  setOptions(question) {
    try {
      question.options = question.type == Base.getQuestionTypeName('type1') || question.type == Base.getQuestionTypeName('type3') ? question.row.slice(6).map(item => (item?.startsWith('*') ? item.slice(1)?.trim(): item.trim())):
        question.type == Base.getQuestionTypeName('type2') ? [true, false]: null;

      if(question.options && question.options.length < 2)
        return this.setError(`Please provide at least 2 options in question ${question.number}`);
      return true;
    } catch(error) {
      console.error(error);
      return this.setError('Error occured');
    }
  }

  setAnswers(question) {
    try {
      question.answer = question.type == Base.getQuestionTypeName('type1') ? question.row.slice(6).find(item => item.startsWith('*'))?.slice(1)?.trim():
        question.type == Base.getQuestionTypeName('type2') ? Boolean(question.row[7]?.trim()): null;

      if(question.answer === undefined)
        return this.setError(`Please provide an answer to question ${question.number}`);

      question.answers = question.type == Base.getQuestionTypeName('type3') ? question.row.slice(6).filter(item => item.startsWith('*')).map(item => (item.startsWith('*') ? item.slice(1).trim(): item.trim())):
        null;

      if(question.answers && question.answers.length == 0)
        return this.setError(`Please provide at least one answer in question ${question.number}`);
      return true;
    } catch(error) {
      console.error(error);
      return this.setError('Error occured');
    }
  }

  prepare() {
    try {
      if(!this.validateArray())
        return false;
      

      
      for(let question of this.questions) {
        if(!this.setOptions(question))
          return false;

        if(!this.setAnswers(question))
          return false;

        
      }
      
      return true;
    } catch(error) {
      if(error instanceof ExcelUploadQuestionTypeHelper.QuestionTypeError)
        return this.setError(error.message);
      console.error(error);
      return this.setError("Error occured");
    }
  }
}

module.exports = XlsxUploadHelper;