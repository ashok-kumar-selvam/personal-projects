import { ObjectId } from "mongodb";
import Socket from "../Interfaces/Socket";

export default function(socket: Socket) {
  socket.on('answer', async (data) => {
    try {
      if(!socket.questionHandler)
        return socket.emit('error', 'Invalid request');

      const _id = new ObjectId(data.id);
      await socket.questionHandler.updateAnswer(_id, data.answer);
      

      

    } catch(error: any) {
      console.error(error);
      socket.emit('error', 'server error');
    }
  })
}