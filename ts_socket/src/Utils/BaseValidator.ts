import {assignmentValidationErrorActions}from './Types';

class BaseValidator {
  protected error: {action: assignmentValidationErrorActions; message: string} = {action: 'error', message: 'Empty error'};


  setError(action: assignmentValidationErrorActions, message: string): false {
    this.error = {action, message};
    return false;
  }

  getError(): typeof this.error {
    return this.error;
  }

  getErrorMessage(): string {
    return this.error.message;
  }
  
  shuffleArray(array: any[]): Array<any>  {
    for(let i = array.length-1; i > 0; i--) {
      let j = Math.floor(Math.random()*(i+1));
      [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
  }
  
  shuffleMatch(array: any[]): Array<any>  {
    for(let i = array.length-1; i > 0; i--) {
      let j = Math.floor(Math.random()*(i+1));
      let k = Math.floor(Math.random()*(i+1));
      
      [array[i].question, array[j].question] = [array[j].question, array[i].question];
      [array[j].answer, array[k].answer] = [array[k].answer, array[j].answer];
      
    }
    return array;
  }
}

export default BaseValidator;