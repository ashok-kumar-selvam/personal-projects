const { default: S } = require("fluent-json-schema");
const countArrayValues = require("../../../helpers/countArrayValues");

module.exports = {
  schema: {
    params: S.object().additionalProperties(false)
    .prop('attempt_id', S.string().required())
  },

  async handler(req, res) {
    try {
      const {db, ObjectId} = this.mongo;
      const attempt_id = new ObjectId(req.params.attempt_id);
      const attempt = await db.collection('attempts').findOne({_id: attempt_id});
      if(!attempt)
        return res.status(404).send('Unable to find the result');

      const data = {};
      const assignment = await db.collection('assignments').findOne({_id: attempt.assign_id});
      const exam = await db.collection('exams').findOne({_id: assignment.exam_id});
      const user = await db.collection('users').findOne({_id: attempt.member_id});

      data.assignment = {
        examTitle: exam.title,
        assignmentName: assignment.introduction.name,
        assignTo: assignment.assignee.assign_to.replace(/_/g, ' '),
        startTime: assignment.time.start,
        endTime: assignment.time.end,
      };

      data.attempt = {
        memberName: user.name || user.first_name,
        attempt: attempt.attempt,
        status: attempt.status == 'started' ? 'In Progress': attempt.status,
        date: attempt.date,
      };

      const answers = await db.collection('answers').find({attempt_id}).toArray();
      data.answers = answers;
      data.stats = {
        totalQuestions: answers.length,
        attemptedQuestions: countArrayValues(answers, 'has_attempted', 'yes'),
        answeredQuestions: countArrayValues(answers, 'has_answered', 'yes'),
        correctAnswers: countArrayValues(answers, 'is_correct', 'yes'),
        totalTime: assignment.time.duration*60,
        takenTime: attempt.time || 0,
        takenPoints: answers.reduce((count, value) => (count+value.point), 0),

      };

      return res.send(data);

    } catch(error) {
      return res.send(error);
    }
  }
}