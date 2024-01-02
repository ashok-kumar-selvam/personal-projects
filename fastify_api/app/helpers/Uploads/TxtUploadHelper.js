const UploadHelper = require('./UploadHelper');
const Base = require('../../config/Base');

class TxtUploadHelper extends UploadHelper {
  extract() {
    try {
      if(!this.validateType())
        return false;

      const extracted = this.file.toString().match(Base.getRegex('extractText')) ?? [];
      if(extracted.length <= 0)
        return this.setError("Unable to extract any questions from this file. Please check and try again.");

        if(this.questionCount != extracted.length) {
          
          return this.setError(
            extracted.length > this.questionCount ? `There are some issues in the text format. We were able to extract ${extracted.length} questions but you have provided the count as ${this.questionCount}. Please try again.`:
            extracted.length < this.questionCount ? `We were able to extract only ${extracted.length} questions from the file. Please check and try again.`: false
          )
        }
      this.extracted = extracted.map((text, index)  => ({number: index+1, text}));
      return true;
    } catch(error) {
      console.log(error);
      return this.setError('Error occured');
    }
  }

  setType(question) {
    try {
      let tr = Base.testRegex;
      
      const type = tr(question.text, 'type1') ? 'type1':
        tr(question.text, 'type2') ? 'type2':
        tr(question.text, 'type3') ? 'type3':
        false;
      if(!type)
        return this.setError(`Unable to find the question type in question ${question.number}. Please format the text properly.`);

      question.type = Base.getQuestionTypeName(type);
      
      return true;
    } catch(error) {
      console.log(error);
      return this.setError('Error occured');
    }
  }

  setQuestion(question) {
    try {
      question.question = Base.testRegex(question.text, 'questionWithMarks') ? question.text.match(Base.getRegex('questionWithMarks'))[0].replace(/\(\s*-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?\s*\)$/, '').trim():
        Base.testRegex(question.text, 'questionWithoutMarks') ? question.text.match(Base.getRegex('questionWithoutMarks'))[0].replace(/\(\d+\)$/, '').trim():
        Base.testRegex(question.text, 'questionWithOptions') ? question.text.match(Base.getRegex('questionWithOptions'))[0].trim(): // error correction should follow
        Base.testRegex(question.text, 'type2Question') ? question.text.match(Base.getRegex('type2Question'))[0].trim():
        false;

      if(!question.question)
        return this.setError(`Unable to extract the question text in question ${question.number}`);
      return true;
    } catch(error) {
      console.log(error);
      return this.setError('Error occured');
    }
  }

  setPoints(question) {
    try {
      question.point = Base.testRegex(question.text, 'point') ? question.text.match(Base.getRegex('point'))[0].trim(): 1;
      question.mpoint = Base.testRegex(question.text, 'mpoint') ? question.text.match(Base.getRegex('mpoint'))[0].trim(): 0;
      return true;
    } catch(error) {
      console.log(error);
      return this.setError('Error occured');
    }
  }

  setOptions(question) {
    try {
      if(question.type === Base.getQuestionTypeName('type1') || question.type === Base.getQuestionTypeName('type3')) {
        const match = question.text.match(Base.getRegex('options'));
        if(match.length <= 0)
          return this.setError(`In question ${question.number}, Please provide at least 2 options. If already provided, Please check the format and try again. `);

        if(match.length < 2)
          return this.setError(`Please provide at least 2 options in question ${question.number}`);

        question.options = match.map(option => option.trim());
      } else if(question.type === Base.getQuestionTypeName('type2')) {
        question.options = [true, false];
      }
      return true;
    } catch(error) {
      console.error(error);
      return this.setError('Error occured');
    }
  }

  setAnswers(question) {
    try {
      question.answer = question.type == Base.getQuestionTypeName('type1') && Base.testRegex(question.text, 'type1Answer') ? question.text.match(Base.getRegex('type1Answer'))[1].trim():
        question.type == Base.getQuestionTypeName('type2') && Base.testRegex(question.text, 'type2Answer') ? question.text.match(Base.getRegex('type2Answer'))[0].trim():
        null;

      question.answers = question.type == Base.getQuestionTypeName('type3') && Base.testRegex(question.text, 'type3Answer') ? question.text.match(Base.getRegex('type3Answer')).map(option => option.trim()):
      null;

      if(question.answer === null && question.answers == null && question.answers.length < 1)
        return this.setError(`In question ${question.number}, You forgot to mention the answer or not properly formatted. Please check and try again.  `);
      return true;
    } catch(error) {
      console.error(error);
      return this.setError('Error occured');
    }
  }

  setOthers(question) {
    try {
      question.explanation = Base.testRegex(question.text, 'explanation') ? question.text.match(Base.getRegex('explanation'))[0].trim(): "";
      return true;
    } catch(error) {
      console.error(error);
      return this.setError('error occured');
    }
  }

  prepare() {
    try {
      if(!this.extract())
        return false;
      
      for(let question of this.extracted) {
        if(!this.setType(question))
          return false;

        if(!this.setQuestion(question))
          return false;

        if(!this.setPoints(question))
          return false;

        if(!this.setOptions(question))
          return false;

        if(!this.setAnswers(question))
          return false;

        if(!this.setOthers(question))
          return false;

        delete question['text'];

      }
      
      this.questions = this.extracted;
      return true;
    } catch(error) {
      console.log(error);
      return this.setError('error occured');
    }
  }
}
module.exports = TxtUploadHelper;