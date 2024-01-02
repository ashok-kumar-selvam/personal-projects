import { Socket as Websocket} from 'socket.io';
import User from './User';
import {ObjectId, MongoClient, Db} from 'mongodb';
import QuestionHandler from '../Utils/QuestionHandler';

export default interface Socket extends Websocket {
  user?: User;
  assign?: any;
  validatedData?: any;
  isValidated?: boolean;
  isResumable?: boolean;
  resumableOnly?: boolean;
  questionHandler?: QuestionHandler;
  mongo?: {
    db: Db;
    client: MongoClient;
    ObjectId: typeof ObjectId;
  };

}