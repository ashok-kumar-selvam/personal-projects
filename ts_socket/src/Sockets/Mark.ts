import { ObjectId } from "mongodb";
import Socket from "../Interfaces/Socket";

export default function(socket: Socket) {
  try {
    socket.on('mark', async (data) => {
      if(!socket.questionHandler)
        return socket.emit('validation_error', 'mark');
      
      const _id = new ObjectId(data.id);
      await socket.questionHandler.updateMark(_id, data.marked);
      console.log('the mark function is success');
    })
  } catch(error: any) {
    console.error(error);
    socket.emit('error', error.message);
  }
}