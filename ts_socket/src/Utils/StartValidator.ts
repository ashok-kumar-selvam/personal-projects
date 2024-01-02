import AssignmentValidator from "./AssignmentValidator";
import { ObjectId } from "mongodb";
import Answer from "../Interfaces/Answer";

class StartValidator extends AssignmentValidator {
  protected attempt_id: ObjectId | undefined;
  public validatedData: any;

  async initiate(): Promise<boolean>  {
    try {
      if(! await this.validate())
        return false;

        if(this.resumableOnly)
          return this.setError('error', 'You cannot start a new exam in this attempt. ');

        await this.db.collection('attempts').updateMany({assign_id: this.assign._id, member_id: this.member_id, status: 'started'}, {$set: {status: 'interrupted'}});
        const {insertedId} = await this.db.collection('attempts').insertOne({
          assign_id: this.assign._id, 
          member_id: this.member_id, 
          exam_id: this.assign.exam_id,
          admin_id: this.assign.user_id,
          attempt: this.attemptedCount+1, 
          status: 'started'});

      this.attempt_id = insertedId;
      return true;
    } catch(error: any) {
      console.error(error);
      return this.setError('error', 'Unexpected server error. ')
    }
  }

  async prepare() {
    try {
      if(! await this.initiate())
        return false;

      const questions = await this.db.collection('questions').aggregate([
        { $lookup: {
          from: 'questionPaper',
          localField: '_id',
          foreignField: 'question_id',
          as: 'questionPaper'
        }},
        { $match: {
          'questionPaper.entity_id': this.assign.exam_id
        }},
        { $project: {
          _id: 0, question_id: '$_id', type: 1, question: 1, options: 1, answer: 1, answers: 1,
          point: 1,  settings: 1, 
        }}
      ]).toArray();

      if(this.assign.question.random == 'yes')
        this.shuffleArray(questions);

      const last = questions.length;
      const answers = questions.map((question, index) => {
        const number = index+1;
        let q: Answer  = {
          question_id: question.question_id,
          attempt_id: this.attempt_id,
          number: index+1,
          type: question.type,
          question: question.question,
          point: 0,
          settings: question.settings,
          time: 0,
          has_attempted: 'no',
          has_answered: 'no',
          has_marked: 'no',
          has_previous: number > 1 ? 'yes': 'no',
          has_next: number >= last ? 'no': 'yes',
        };

        if(question.type === 'match_it') {
          q.options = this.shuffleMatch(question.answers);
          q.answer = q.options;
        }

        const optionsTypes = ['single_choice', 'multi_choice', 'true_or_false'];
        if(this.assign.question.options == 'random' && optionsTypes.includes(q.type))
          q.options = this.shuffleArray(question.options);

          q.answer = q.type == 'single_choice' || q.type == 'true_or_false' ? '':
          q.type == 'multi_choice' || q.type == 'fill_the_blanks' ? []:
          q.type == 'cloze' ? this.shuffleArray(question.answer):
          q.type == 'error_correction' ? q.question:
          q.type == 'description' ? {text: null, file: {id: null, name: null}}: '';
          return q;
      });

      await this.db.collection('answers').insertMany(answers);
      return true;
    } catch(error: any) {
      console.error(error);
      return this.setError('error', 'Unexpected server error ')
    }
  }

  async process(): Promise<boolean> {
    try {
      if(! await this.prepare())
        return false;

      const settings = {
        id: this.assign._id,
        attempt: this.attemptedCount+1,
        attempt_id: this.attempt_id,
        title: this.assign.introduction.name,
        total_questions: this.questionCount,
        time_limit: this.assign.time.duration,
        question_time: this.assign.question.time,
      };
      this.validatedData = settings;
      return true;
    } catch(error: any) {
      console.error(error);
      return this.setError('error', 'Unexpected server error');
    }
  }
}

export default StartValidator;