import BaseValidator from "./BaseValidator";
import Socket from "../Interfaces/Socket";
import { Db, ObjectId} from "mongodb";
import { answerIdOrNumber } from "./Types";
import Question from "../Interfaces/Question";
import Timer from "../Interfaces/Timer";
import { Update } from "../Interfaces/Answers.interfaces";

class QuestionHandler extends BaseValidator {
  private attempt_id: ObjectId;
  private db: Db;
  private question: any;
  private questionTime: number;
  private questionCount: number;
  private timers: Map<string, Timer>;
  private examTime: number = 0;

  constructor(attempt_id: ObjectId, db: Db, questionTime: number, questionCount: number, examTime?: number) {
    super();
    this.attempt_id = attempt_id;
    this.db = db;
    this.questionTime = questionTime;
    this.questionCount = questionCount;
    this.timers = new Map();
    if(examTime)
      this.examTime = examTime;
  }

  async getQuestion(data: answerIdOrNumber, action: string, strict: boolean): Promise<Question | false>  {
    try {
      const attempt_id = this.attempt_id;
      let query: any;
      // we are focusing on the question limit and if it is greater than 0
      if(this.questionTime > 0 && strict === false) { // if strict equals to true, we do not want to modify the normal behavier.
        let additional = {time: {$lt: this.questionTime}, attempt_id};
        query = data.number ? {...additional, number: action === 'next' ? {$gte: data.number}: action === 'previous' ? {$lte: data.number}: data.number}:
        data?.answer_id ? {_id: data.answer_id}:
        additional;
      } else {
        query = data?.number ? {number: data.number, attempt_id}:
        data?.answer_id ? {_id: data.answer_id, attempt_id}:
        {attempt_id};

      }


      const questions = await this.db.collection('answers').aggregate([
        { $lookup: {
          from: 'questions',
          localField: 'question_id',
          foreignField: '_id',
          as: 'relatedQuestion'
        }},
        { $match : query},
          { $project: {
            _id: 0, id: '$_id', question: 1, type: 1, options: 1, answer: 1, has_marked: 1, has_previous: 1, has_next: 1, time: 1, 
            has_attempted: 1, number: 1, attempt_id: 1, question_id: 1, given_point: {$arrayElemAt: ['$relatedQuestion.point', 0]}, 
            choices: {$arrayElemAt: ['$relatedQuestion.answer', 0]}, settings: {$arrayElemAt: ['$relatedQuestion.settings', 0]},
            
            
          }}
        ]).sort({number: action === 'previous' ? -1: 1}).toArray();

      if(!questions[0])
        return this.setError('notfound', 'Unable to find the question');

      const question: Question = questions[0] as Question;

      question.choices = question?.settings?.provide_options == 'yes' ? this.shuffleArray(question.choices as any[]): false;
      this.question = question;
      return question;
    } catch(error:any ) {
      console.error(error);
      return this.setError('error', 'Unexpected server error');
    }
  }

  async getNext(number: number): Promise<Question | false> {
    try {
      if(number >= this.questionCount)
        return this.setError('nonext', 'There is no next question. ');

      return await this.getQuestion({number: number+1}, 'next', false);
    } catch(error: any) {
      console.error(error);
      return this.setError('error', 'Unexpected server error')
    }
  }

  async getPrevious(number: number): Promise<Question | false >  {
    try {
      if(number <= 0)
        return this.setError('noprevious', 'There is no previous question.');
      return  await this.getQuestion({number: number-1}, 'previous', false);
    } catch(error: any) {
      console.error(error);
      return this.setError('error', 'Unexpected server error. ')
    }
  }

  async allQuestions(type: string): Promise<any[] | false> {
    try {
      
      let query: any  = this.questionTime > 0 ? {attempt_id: this.attempt_id, time: {$lt: this.questionTime}}: {attempt_id: this.attempt_id};
      query = type === 'marked' ? {...query, has_marked: 'yes'}:
      type === 'answered' ? {...query, has_answered: 'yes'}:
      type === 'unanswered' ? {...query, has_answered: 'no'}:
      type === 'unattempted' ? {...query, has_attempted: 'no'}: query;
      
      const options = {
        sort: {"number": 1},
        projection: {_id: 0, id: '$_id', time: 1, number: 1}
      };
      const questions = await this.db.collection('answers').find(query, {
        sort: {number: 1},
        projection: {_id: 0, id: '$_id', number: 1, time: 1}
      }).toArray();
      return questions;

    } catch(error: any) {
      console.error(error);
      return this.setError('error', 'Server error');
    }
  }

  async updateAnswer(_id: ObjectId, answer: any): Promise<void> {
    try {
      const data: Update = {
        answer: answer,
        has_attempted: 'yes'
      };

      const time = await this.getTimerValue(_id);
      if(time > 0)
        data.time = time;

      await this.db.collection('answers').updateOne({_id}, {$set: data});

    } catch(error: any) {
      console.error(error);
    }
  }

  async updateMark(_id: ObjectId, has_marked: 'yes' | 'no'): Promise<void> {
    try {
      const time = this.getTimerValue(_id);
      let answer = time > 0 ? {time, has_marked}: {has_marked};
      await this.db.collection('answers').updateOne({_id}, {$set: answer});

    } catch(error: any) {
      console.error(error);
    }
  }
  setTimer(id: ObjectId, socket: Socket, type: 'exam' | 'question', initial?: number): void {
    try  {
      const idString: string = id.toString();
      if(this.timers.has(idString))
        return;

      
      const data: Timer = {
        time: initial || 0,
        limit: type === 'question' ? this.questionTime: this.examTime*60,
        intervalId: null
      };

      data.intervalId = setInterval( () => {

        data.time++;
        let timeValue: number = data.limit === 0 ? data.time:
        data.limit > 0 && data.time < data.limit ? data.limit-data.time:
        data.time;
        
        if(type === 'question' && data.limit > 0 && data.time >= data.limit) {
          this.handleQuestionTimeout(id, socket, data.time);
        } else if(type === 'exam' && data.limit > 0 && data.time >= data.limit) {
          this.db.collection('attempts').updateOne({_id: id}, {$set: {status: 'timeout', time: data.time}});
          this.clearTimer(id);
          //socket.off('answer');
          socket.emit('exam_timeout');
          this.clearAllTimers();
          socket.disconnect();
        }
        socket.      emit(`${type}_timer`, timeValue);
      }, 1000);
      this.timers.set(idString, data);
    } catch(error: any) {
      console.error(error);
    }
  }

  clearTimer(id: ObjectId): void {
    try {
      const idString: string = id.toString();
      console.log('the clear interval is running. ');
      if(!this.timers.has(idString))
        return;
      const data = this.timers.get(idString);
      
      if(!data?.intervalId)
        return;
      clearInterval(data.intervalId);
      this.timers.delete(idString);
    } catch(error: any) {
      console.error(error);
    }
  }

  getTimerValue(id: ObjectId): number {
    const idString = id.toString();
    
    const data = this.timers.get(idString);
    
    if(!data?.time)
      return 0;



    return data.time;
  }

  async handleQuestionTimeout(answer_id: ObjectId,  socket: Socket, time: number) {
    try {
      const currentQuestion = await this.getQuestion({answer_id}, 'current', true);
      if(currentQuestion === false) {
        const error = this.getError();
        return socket.emit(error.action as string, error.message);

      }
await this.db.collection('answers').updateOne({_id: answer_id}, {$set: {time}});
await this.clearTimer(answer_id);

      const nextQuestion = await this.getNext(currentQuestion.number);
      if(nextQuestion === false) {
        const questions = await this.allQuestions('marked');
        const error = await this.getError();
        if(questions === false)
          return socket.emit('error', error.message);

        if(questions.length <= 0) {
          socket.emit('question_timeout');
          this.clearAllTimers();
          return socket.disconnect();
        }

        return socket.emit('confermation', questions);
      }
      
      await this.setTimer(nextQuestion.id, socket, 'question', nextQuestion.time);
      socket.emit('question', nextQuestion);

    } catch(error: any) {
      console.error(error);
    }
  }

  clearAllTimers() {
    try {
      for(let value of this.timers.values()) {
        if(value.intervalId)
          clearInterval(value.intervalId);
      }
      console.log('the intervals are cleared')
    } catch(error: any) {
      console.error(error);
    }
  }
}
export default QuestionHandler;