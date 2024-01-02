import UpdateObject, {UpdateData} from "../Interfaces/UpdateObject";
import CorrectionObject from "../Interfaces/CorrectionObject";
import BaseValidator from "./BaseValidator";
import { ObjectId } from "mongodb";

class AnswerValidator extends BaseValidator {
  public errors: string[] = [];
  
  addError(error: string): 0 {
    this.errors.push(error);
    return 0;
  }

  singleChoice({givenAnswer, correctAnswer, givenPoint}: CorrectionObject): number {
    try {
      
      if(typeof givenAnswer !== 'string' && typeof givenAnswer !== 'boolean' && typeof givenAnswer !== 'number')
        return this.addError('Invalid answer type for single choice question. ');

      return givenAnswer== correctAnswer? givenPoint: 0;
    } catch(error: any) {
      console.error(error);
      return this.addError('server error');
    }
  }

  multiChoice({givenAnswer, correctAnswer, givenPoint, fraction}: CorrectionObject): number {
    try {
      
      if(!(givenAnswer instanceof Array))
        return this.addError('Invalid answer for multi choice question. ');
      return  givenAnswer.reduce((score, value) => (correctAnswer.includes(value.toString().trim()) ? score+fraction: score), 0);
    } catch(error: any) {
      console.error(error);
      return this.addError('Server error');
    }
  }

  fillTheBlanks({givenAnswer, correctAnswer, givenPoint}: CorrectionObject): number {
    try  {
      if(typeof givenAnswer !== 'string')
        return this.addError('Invalid answer for fill the blanks question ');

      return correctAnswer.includes(givenAnswer) ? givenPoint: 0;

    } catch(error: any) {
      console.error(error);
      return this.addError('Server error');
    }
  }

  matchIt({givenAnswer, givenPoint, correctAnswer, fraction}: CorrectionObject): number {
    try  {
      if(!(givenAnswer instanceof Array))
        return this.addError('Invalid answer for match it question.');
      return correctAnswer.reduce((score: any, obj: any) => ( (givenAnswer.some(ans => (ans.question == obj.question && ans.answer == obj.answer)) ? score+fraction: score)), 0);
    } catch(error: any) {
      console.error(error);
      return this.addError('server error');
    }
  }

  cloze({givenAnswer, givenPoint, correctAnswer, fraction = 0}: CorrectionObject): number {
    try  {
      if(!(givenAnswer instanceof Array))
        return this.addError('Invalid answer for cloze question. ');

      return givenAnswer.reduce((score: number, value: any, index: number) => (givenAnswer[index] === correctAnswer[index] ? score+fraction: score), 0);
    } catch(error: any) {
      console.error(error);
      return this.addError('server error')
    }
  }

  descriptive({givenAnswer}: CorrectionObject): number {
    try  {
      return 0;
    } catch(error: any) {
      console.error(error);
      return this.addError('server error');
    }
  }
}

export default AnswerValidator;