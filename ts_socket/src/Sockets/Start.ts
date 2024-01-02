import QuestionHandler from "../Utils/QuestionHandler";
import StartValidator from "../Utils/StartValidator";
import Socket from "../Interfaces/Socket";
import TimerHelper from "../Utils/TimerHelper";

export default async function(socket: Socket) {
  socket.once('start', async (assign_id: string) => {
    try  {
      if(!socket?.mongo?.db)
        return socket.emit('error', 'Error in database configuration. ');

      const validator = new StartValidator(assign_id, socket.mongo.db, socket.user);

      if(! await validator.process())
        return socket.emit('error', validator.getError());

      socket.isValidated = true;
      const settings = validator.validatedData;
      const questionHandler = new QuestionHandler(settings.attempt_id, socket.mongo.db, settings.question_time, settings.total_questions, settings.time_limit);
      socket.questionHandler = questionHandler;
      socket.data = settings;

      const question = await questionHandler.getQuestion({}, 'current', true);
      if(!question)
        return socket.emit('error', questionHandler.getError());
      questionHandler.setTimer(settings.attempt_id, socket, 'exam');
      questionHandler.setTimer(question.id, socket, 'question');
      return socket.emit('start', {settings, question});

    } catch(error: any) {
      console.error(error);
      return socket.emit('error', 'Unexpected error occured.')
    }
  })
}