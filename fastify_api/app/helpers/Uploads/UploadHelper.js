const schemas = require('./UploadQuestionSchemas');
const Base = require('../../config/Base');

class UploadHelper {
  
  constructor(questionCount, fileType, entityType, data) {
    this.questionCount = questionCount;
    this.fileType = fileType;
    this.file= data.questions.data;
    this.data = data;
    this.entityType = entityType;
    this.allowedMimetypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/plain'];
    this.allowedQuestionTypes = Base.getSupportedQuestionTypesByEntity(entityType);
  }

  setError(error) {
    this.error = error;
    return false;
  }

  getError() {
    return this.error;
  }

  validateType() {
    try {
      if(!this.allowedMimetypes.includes(this.data.questions.mimetype))
        return this.setError("The upload operation only supports excel and txt files.");

      this.type = this.data.questions.mimetype == this.allowedMimetypes[0] ? 'xlsx':
        this.data.questions.mimetype == this.allowedMimetypes[1] ? 'txt': 'unknown';

      if(this.fileType != this.type)
        return this.setError("There are some issues with file type. please select the correct file type that you are uploading.");
      return true;
    } catch(error) {
      console.error(error);
      return this.setError("Error occured");
    }
  }

  validateQuestionCount(questionCount, questions) {
    let count = questions.length;
    if(questionCount === count)
      return true;
    
    if(questionCount > count)
      return this.setError(`We were able to extract only ${count} questions from the file.`);

    if(questionCount < count)
      return this.setError(`The file  seems to have some error and we were able to extract ${count} questions.`);
  }


  isEmpty(value) {

    return value.trim().length === 0;
  }

  getQuestions() {
    return this.questions;
  }

  static getJoiErrorMessage(error) {
    for(let detail of error.details)

      return detail.message;
    return 'unknown';
  }

  static validate(questions) {
    let newQuestions = [];
    for(let question of questions) {
      let number = question.number;
      delete question['number'];
      const {error, value} = schemas[Base.getQuestionTypeKey(question.type)].validate(question, {stripUnknown: true});
      if(error)
        return [false, `Question ${number}: `+this.getJoiErrorMessage(error)];

      newQuestions.push(value);
    }
    return [true, newQuestions];
  }
}

module.exports = UploadHelper;