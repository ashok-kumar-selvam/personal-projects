import BaseValidator from "./BaseValidator";
import { ObjectId, Db } from "mongodb";
import AnswersFromQuestions from "../Lookups/AnswersFromQuestions";
import CorrectionObject from "../Interfaces/CorrectionObject";
import UpdateObject, {UpdateData} from "../Interfaces/UpdateObject";
import AnswerValidator from './AnswerValidator'
import ResultStats from "../Interfaces/ResultStats";
import TimerHelper from "./TimerHelper";

class ResultHandler extends AnswerValidator  {
  public attempt_id: ObjectId;
  private db: Db;

constructor(attempt_id: ObjectId, db: Db) {
    super();
    this.attempt_id = attempt_id;
    this.db = db;
  }

  getPoint(data: CorrectionObject): number {
    try {
      data.fraction = Array.isArray(data.correctAnswer) ? (data.givenPoint/data.correctAnswer.length): 0;
      const {type} = data;
      return type === 'single_choice' || type === 'true_or_false' ? this.singleChoice(data):
      type === 'multi_choice' ? this.multiChoice(data):
      type === 'fill_the_blanks' || type === 'error_correction' ? this.fillTheBlanks(data):
      type === 'match_it' ? this.matchIt(data):
      type === 'cloze' ? this.cloze(data): 0;
    } catch(error: any) {
      console.error(error);
      return this.addError('server error');
    }
  }

  getIsCorrect(takenPoint: number, {givenPoint, type, settings = {}}: CorrectionObject): 'yes' | 'no' | 'partial' | 'review' {
    try  {
      return takenPoint === 0 ? 'no':
      takenPoint === givenPoint ? 'yes':
      (takenPoint > 0 && takenPoint < givenPoint && settings?.point_type && settings.point_type == 'partial') ? 'partial':
      type == 'descriptive' && takenPoint === 0 ? 'review': 'no';
    } catch(error: any) {
      console.error(error);
      return 'no';
    }
  }

  getHasAnswered({givenAnswer}: CorrectionObject): 'yes' | 'no' {
    try  {
      return givenAnswer === null || givenAnswer === undefined ? 'no':
      typeof givenAnswer === 'string' && givenAnswer.trim() === '' ? 'no':
      Array.isArray(givenAnswer) && givenAnswer.length === 0 ? 'no':
      givenAnswer instanceof Object && givenAnswer.text === null && givenAnswer?.file?.id === null ? 'no': 'yes';
    } catch(error: any) {
      console.error(error);
      return 'no';
    }
  }

  setUpdateData(id: ObjectId, data: UpdateData): UpdateObject {

      return {
        updateOne: {
          filter: {_id: id},
          update: {$set: data}
        }
      }
    
  }

  async process(): Promise<boolean> {
    try {
      const answers = await this.db.collection('questions').aggregate([
        { $lookup: {
          from: 'answers',
          foreignField: 'question_id',
          localField: '_id',
          as: 'realAnswer'
        }},
        {$unwind: '$realAnswer'},
        { $project: {
          id: '$realAnswer._id',
          givenPoint: '$point',
          correctAnswer: {$ifNull: ['$answers', '$answer']},
          givenAnswer: '$realAnswer.answer',
          mpoint: 1, settings: 1, type: 1

        }}
      ]).toArray();

      const updateObjects: UpdateObject[] = answers.map((data : any) => {
        const point = this.getPoint(data);
        const is_correct = this.getIsCorrect(point, data);
        const has_answered = this.getHasAnswered(data);
        return this.setUpdateData(data.id, {point, is_correct, has_answered});
        });

      const  {modifiedCount} = await this.db.collection('answers').bulkWrite(updateObjects);

      if(modifiedCount !== updateObjects.length)
        return this.setError('error', `Only ${modifiedCount} result were updated and it is an error.`);
      return true;
    } catch(error: any) {
      console.error(error);
      this.setError('error', error.message)
      return false;
    }
  }

  async getStats(attempt_id: ObjectId, db: Db): Promise<ResultStats> {
    const answers = await db.collection('answers').find({attempt_id}).toArray();
    let [attempted_questions, answered_questions,  taken_points,   correct_answers, Answers_to_review] = [0, 0, 0, 0, 0];
    for(let answer of answers) {
      attempted_questions += answer.has_attempted === 'yes' ? 1: 0;
      answered_questions += answer.has_answered === 'yes' ? 1: 0;
      taken_points += answer.point;
      
      correct_answers += answer.is_correct === 'yes' ? 1: 0;
      Answers_to_review += answer.is_correct === 'review' ? 1: 0;
    }

    const taken_time = TimerHelper.getTime(attempt_id);
    return {attempted_questions, answered_questions, taken_points,  correct_answers, Answers_to_review, taken_time};
    
  }
}

export default ResultHandler;