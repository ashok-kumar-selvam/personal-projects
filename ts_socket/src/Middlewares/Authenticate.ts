import Socket from '../Interfaces/Socket';
import {JwtPayload, Secret, verify} from 'jsonwebtoken';
import User from '../Interfaces/User';

export default async function(socket: Socket, next: any) {
  try {
    const token: string  = socket.handshake.auth.token;
    if(!process.env.JWT_KEY)
      throw new Error('The required JWT_KEY environment is not set. ');
    const user = await verify(token, process.env.JWT_KEY) as User;
    socket.user = user;
    console.log('the authenticate is success. ');
    await next();
  } catch(error: any) {
    console.log('error occured in the authenticate middleware. ', error)
    next();
  }
}