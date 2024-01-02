import Socket from "../Interfaces/Socket";

export default function(socket: Socket) {
  socket.on('confermation', async () => {
    try {
      if(!socket.questionHandler)
        return socket.emit('error', 'Unable to validate the request.');

      const questions = await socket.questionHandler.allQuestions('marked');
      
      if(questions === false)
        return socket.emit('confermation', []);
      return socket.emit('confermation', questions);
    } catch(error: any) {
      console.error(error);
      socket.emit('error', 'server error');
    }
  })
}