import { ObjectId } from "mongodb";
import Socket from "../Interfaces/Socket";
import ResultHandler from "../Utils/ResultHandler";
import TimerHelper from "../Utils/TimerHelper";

export default async function(socket: Socket) {
  socket.on('submit', async (attempt_id: string) => {
    try  {
      if(!socket.mongo || !socket.data|| !socket.questionHandler)
        return socket.emit('error', 'Unable to find the required data. the request may not be validated. ');

      const {data, mongo, questionHandler} = socket;
      const {attempt_id} = data;
      const {db} = mongo;
      const attempt = await db.collection('attempts').findOne({_id: attempt_id});
    
      if(!attempt)
        return socket.emit('error', "The attempt hasn't been registered. ");

      const validator = new ResultHandler(attempt_id, db);
      if(!validator.process())
        return socket.emit('error', 'some error occured. Do not worry, your responses are saved. ');

      if(validator.errors.length > 0)
        return socket.emit('error', 'The results are saved but encountered some errors.');
      const time = await socket.questionHandler.getTimerValue(attempt_id);
        await db.collection('attempts').updateOne({_id: attempt_id}, {$set: {status: 'completed', time}});
      const result = {        
        attempt_id: attempt_id,
        assign_id: attempt.assign_id,
        admin_id: attempt.admin_id,
        member_id: attempt.member_id,
        exam_id: attempt.exam_id,
        stats: await validator.getStats(attempt_id, db),
      };

      socket.questionHandler.clearTimer(attempt_id);
      await mongo.db.collection('results').insertOne(result);
      return socket.emit('finish');
    } catch(error: any) {
      console.error(error);
      return socket.emit('error', error.message)
    }
  })
}