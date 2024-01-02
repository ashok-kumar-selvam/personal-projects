import Socket from "../Interfaces/Socket";
import { ObjectId } from "mongodb";

interface TimerData {
  time: number;
  intervalId: NodeJS.Timeout | null;
}

class TimerHelper {
  static timers: Map<string, TimerData> = new Map();

  static setTimer(id: ObjectId, socket: Socket, type: 'exam' | 'question', initial?: number) {
    
    if(!this.timers.has(id.toString())) {
      
      
      let timerData: TimerData = {
        time: initial || 0,
        intervalId: null
      };
      timerData.intervalId = setInterval(() => {
        timerData.time++;
        socket.emit(`${type}_timer`, timerData.time);
      }, 1000);
      this.timers.set(id.toString(), timerData);

    }
  }

  static clearTimer(id: ObjectId): void {
    if(this.timers.has(id.toString())) {
      const data = this.timers.get(id.toString());
      
      if(data?.intervalId)
        clearInterval(data?.intervalId);

      this.timers.delete(id.toString());
    } else {
      console.log('the data with this id is not found', id);
    }
      
  }

  static getTime(id: ObjectId): number  {
    let data = this.timers.get(id.toString());
    if(!data || !data.time)
    return 0;
  return data.time;
  }
}

export default TimerHelper