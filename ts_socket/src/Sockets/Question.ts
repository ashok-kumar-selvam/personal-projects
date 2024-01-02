import { ObjectId } from "mongodb";
import Socket from "../Interfaces/Socket";
import Schemas from "../Utils/Schemas";
import TimerHelper from "../Utils/TimerHelper";
import { Update } from "../Interfaces/Answers.interfaces";
import QuestionHandler from "../Utils/QuestionHandler";
import Question from "../Interfaces/Question";

export default async function(socket: Socket) {
  socket.on('question', async (data) => {
    try {
      // check whether socket has the required properties
      if(!socket.questionHandler)
        return socket.emit('validation_error', 'Please validate the request to continue.');


      const value = data;
      // check whether the question with the provided id is really exists 
      const answer_id = new ObjectId(value.id);
      const qh = socket.questionHandler;
      const currentQuestion = await qh.getQuestion({answer_id}, 'current', true);
      if(!currentQuestion) 
        return socket.emit('error', qh.getErrorMessage());
      
      
      
      // get the required question and check the action and send error if required
      const action = value.action;
      const newQuestion: Question | false |  null  = action === 'next' ? await qh.getNext(currentQuestion.number):
        action === 'previous' ? await qh.getPrevious(currentQuestion.number):
        action === 'current' && data?.answer_id ? await qh.getQuestion({answer_id: new ObjectId(data.answer_id)}, 'current', true): null;
        

      if(newQuestion === false) 
        return socket.emit('error', qh.getErrorMessage());
      
      
      
      if(newQuestion === null)
        return;

      qh.clearTimer(currentQuestion.id);
      qh.setTimer(newQuestion.id, socket, 'question', newQuestion.time);
      return socket.emit('question', newQuestion);
    } catch(error: any) {
      console.error(error);
      socket.emit('error', 'Error in server.')
    }
  })
}