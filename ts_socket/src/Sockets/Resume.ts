import { ObjectId } from "mongodb";
import Socket from "../Interfaces/Socket";
import QuestionHandler from "../Utils/QuestionHandler";

export default async function(socket: Socket) {
  socket.on('resume', async (assignId) => {
    try {
      if(!socket.validatedData || !socket.mongo || !socket.user)
        return socket.emit('error', 'Invalid request');

      const db = socket.mongo.db;

      const assign_id = new ObjectId(assignId);
      const member_id = new ObjectId(socket.user.uuid);
      const attempt = await db.collection('attempts').findOne({status: 'started', assign_id, member_id});
      if(!attempt)
        return socket.emit('error', 'Unable to find the attempt');

      const assign = await db.collection('assignments').findOne({_id: attempt.assign_id});
      if(!assign)
        return socket.emit('error', 'Unable to find the exam/assignment');

      const settings = {
        id: assign._id,
        attempt: attempt.attempt,
        attempt_id: attempt._id,
        title: assign.introduction.name,
        total_questions: socket.validatedData.questions,
        time_limit: assign.time.duration,
        question_time: assign.question.time,
      };

      const questionHandler = new QuestionHandler(attempt._id, db, settings.question_time, settings.total_questions, settings.time_limit);
      const questionObject = await db.collection('answers').findOne({attempt_id: attempt._id, has_attempted: 'yes'}, { sort: {number: -1}});

      const question = questionObject ? await questionHandler.getQuestion({answer_id: questionObject._id}, 'current', true): await questionHandler.getQuestion({}, 'current', true);
      if(question === false)
        return socket.emit('error', 'Unable to find the question. please contact technical team.');

        socket.data = settings;
        socket.questionHandler = questionHandler;

      questionHandler.setTimer(attempt._id, socket, "exam", attempt.time);
      questionHandler.setTimer(question.id, socket, "question", question.time);
      socket.emit('start', {settings, question});
    } catch(error: any) {
      console.error(error);
      return socket.emit('error', 'internal error');
    }
  })
}