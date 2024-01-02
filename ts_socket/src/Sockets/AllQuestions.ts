import Socket from "../Interfaces/Socket";
import { ObjectId } from "mongodb";

export default function(socket: Socket) {
  try {
    socket.on('allQuestions', async (type: string) => {
      if(!socket.questionHandler)
        return socket.emit('novalidate', 'allquestions');

      const questions = await socket.questionHandler.allQuestions(type);
      if(questions === false)
        return socket.emit('error', socket.questionHandler.getErrorMessage())
      return socket.emit('allQuestions', questions);
    })
  } catch(error: any) {
    console.error(error);
  }
}