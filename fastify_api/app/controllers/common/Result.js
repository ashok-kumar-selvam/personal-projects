const { default: S } = require("fluent-json-schema");
const { getUser } = require("../../helpers/UserHelper");

module.exports = {
  schema: {
    params: S.object().additionalProperties(false)
    .prop('result_id', S.string().required()),
  },

  async handler(req, res) {
    try {
      const user = await getUser(req);
      if(!user || !user.uuid)
        return res.status(400).send('Should be logged in to access.');

      const {db, ObjectId} = this.mongo;
      const member_id = new ObjectId(user.uuid);
      const result = await db.collection('results').findOne({_id: new ObjectId(req.params.result_id)});
      if(!result)
        return res.status(404).send('Unable to find the result.');

      if(user.uuid.toString() != result.member_id.toString())
        return res.status(400).send('You cannot access this result.');

      const assignment = await db.collection('assignments').findOne({_id: result.assign_id});
      if(!assignment)
        return res.status(400).send('Unable to find the relevant assignment. ');

      const now = Math.floor(Date.now()/1000);
      if(assignment.result.method == 'scheduled' && assignment.result.publishOn > now)
        return res.status(400).send('Please wait for the result.');

      const [questionStats] = await db.collection('questionPaper').aggregate([
        { $match: {entity_id: assignment.exam_id}},
        { $lookup: {
          from: 'questions',
          localField: 'question_id',
          foreignField: '_id',
          as: 'question'
        }},
        { $unwind: '$question'},
        { $group: {
          _id: '$entity_id',
          totalQuestions: {$sum: 1},
          totalPoints: {$sum: '$question.point'}
        }},
        { $project: {
          totalQuestions: 1, totalPoints: 1,
          question: 1,
        }}
      ]).toArray();
      console.log('the questionStats is ', questionStats)

      const answers = await db.collection('answers').aggregate([
        { $match: {attempt_id: result.attempt_id}},
        { $lookup: {
          from: 'questions',
          localField: 'question_id',
          foreignField: '_id',
          as: 'originalQuestion'
        }},
        { $unwind: '$originalQuestion'},
        { $project: {
          number: 1, question: 1, options: 1, type: 1, answer: 1, time: 1, point: 1,
          
          has_attempted: 1, has_answered: 1, 
          is_correct: assignment.result.showAnswer === 'yes' ? 1: 'hidden', 
          correct_answer: (assignment.result.showAnswer === 'yes' ? {$ifNull: ['$originalQuestion.answer', '$originalQuestion.answers']}: 'HIDDEN'),
          explanation: assignment.result.showExplanation === 'yes' ? '$originalQuestion.explanation': 'HIDDEN',
        }}
      ]).sort({number: 1}).toArray();
      

      const attempt = await db.collection('attempts').findOne({_id: result.attempt_id});

      const totalPoints = questionStats.totalPoints;
      const takenPoints = answers.reduce((count, value) => (count+value.point), 0);
      const passmark = assignment.general.passmark;
      const percentage = +((takenPoints/totalPoints)*100).toFixed(2);
      const hasPassed = percentage >= passmark ? 'yes': 'no';

      // hide points if specified
      if(assignment.result.showAnswer === 'no')
        answers.forEach((value)  => value.point = 'hidden');
      switch(assignment.result.type) {
        case "pass_or_fail":
          
          return {hasPassed, percentage, type: assignment.result.type};
        break;
        case "simple_result":

          return {
            title: assignment.introduction.name,
            name: user.name,
            type: assignment.result.type,
            totalTime: assignment.time.duration*60,
            takenTime: attempt.time,
            attempt: attempt.attempt,
            completedOn: attempt.date,
            status: attempt.status,

            totalPoints, takenPoints, hasPassed, percentage, passmark

          }
        break;
        case "complete_result":

        return {
          type: assignment.result.type,
          answers: answers,
          assignment: {
            title: assignment.introduction.name,
            start: assignment.time.start,
            end: assignment.time.end,

          },
          attempt: {
            memberName: user.name,
            attempt: attempt.attempt,
            status: attempt.status,
            date: attempt.date,
          },
          stats: {
            totalTime: assignment.time.duration*60,
            takenTime: attempt.time,
            totalPoints, takenPoints, hasPassed, passmark, percentage,
          },
          
        }
      }


    } catch(error) {
      return res.send(error);
    }
  }
}