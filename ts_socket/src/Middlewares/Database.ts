import {ObjectId, MongoClient} from 'mongodb';
import  Socket  from '../Interfaces/Socket';

export default async function(socket: Socket, next: any) {
  try {
    if(!process.env.DATABASE_URL || !process.env.DATABASE_DEVELOPMENT_URL)
      throw new Error('The required DATABASE_URL variable is not set in the environment. ');

    const url = process.env.NODE_ENV == 'development' ? process.env.DATABASE_DEVELOPMENT_URL: process.env.DATABASE_URL;
    const client = new MongoClient(url);
    await client.connect();
    socket.mongo = {
      ObjectId,
      client,
      db: client.db()
    };
    console.log('the database connection established.');
    await next();
  } catch(error: any) {
    console.log('the error occured in database middleware. ', error);
    next(new Error(error.message));
  }
}