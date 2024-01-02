/**
 * Import required modules
 */
// import external dependencies
import { Server, Socket} from 'socket.io';
import dotenv from 'dotenv';
import * as MySocket from './Interfaces/Socket';

// import middlewares
import Authenticate from './Middlewares/Authenticate';
import Database from './Middlewares/Database';
import InputValidate from './Middlewares/InputValidate';

// import socket files
import Validate from './Sockets/Validate';
import Info from './Sockets/Info';
import Start from './Sockets/Start';
import Answer from './Sockets/Answer';
import Submit from './Sockets/Submit';
import AllQuestions from './Sockets/AllQuestions';

import Question from './Sockets/Question';
import Confermation from './Sockets/Confermation';
import Mark from './Sockets/Mark';
import Resume from './Sockets/Resume';

//Initiate dotenv config so that the environment variables are loaded
dotenv.config();

// create a io server with port 3001
const io = new Server(3001, {cors: {origin: 'http://localhost:3000'}});

/**
 * provide all middlewares in the order that they need to run.
 * these middlewares set up the required initial details such as user and mongo property in the socket object.
 */

io.use(Authenticate);
io.use(Database);

io.on('connection', (socket: Socket) => {
  console.log('the connection established. ');
  
  //input validation middleware
  socket.use(InputValidate);
  
  Validate(socket);
  Info(socket);
  Start(socket);
  Resume(socket);
  Answer(socket);
  Submit(socket);
  AllQuestions(socket);
  Mark(socket);
  Question(socket);
  Confermation(socket);

  socket.on('disconnecting', async (reason) => {
    const s: MySocket.default = socket as any;
    if(!s.mongo || !s.data || !s.questionHandler)
      return false;

    const {mongo, data, questionHandler} = s;
    console.log('the attempt_id is ', data.attempt_id);
    const time = await questionHandler.getTimerValue(data.attempt_id);
    if(time > 0)
      await mongo.db.collection('attempts').updateOne({_id: data.attempt_id}, {$set: {time}});
    await questionHandler.clearAllTimers();
    console.log('the time is ', time)
      
      
  })
  socket.on('disconnect', (reason) => {
    console.log('the connection is disconnected and the reason is ', reason);
  });

  socket.on('reconnect',  () => {
    console.log('the connection is reset');
  });

  socket.on('error', (error) => {
    if(error)
    socket.emit('error', error.message);
  })
});
