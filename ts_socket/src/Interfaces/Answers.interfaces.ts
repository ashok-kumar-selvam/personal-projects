import { ObjectId } from "mongodb";

// used while creating new data in the answers collection
export interface Create  {
  attempt_id: ObjectId | undefined;
  question_id: string;
  number: number;
  type: string;
  question: string;
  point: number;
  time: 0;
  options?: any[];
  answer?: any;
  settings?: any;
  has_attempted: 'yes' | 'no';
  has_answered: 'yes' | 'no';
  has_marked: 'yes' | 'no';
  has_previous: 'yes' | 'no';
  has_next: 'yes' | 'no';
}

// used while answer is received from the client 
export interface Update {
  answer: any;
  has_attempted: 'yes' | 'no';
  time?: number;
  has_marked?: 'yes' | 'no';
}

export interface ResultUpdateData {
  point: number;
  is_correct: 'yes' | 'no' | 'partial' | 'review';
  has_answered: 'yes' | 'no';
}

export interface ResultUpdateObject {
  updateOne: {
    filter: {_id: ObjectId},
    update: {$set: ResultUpdateData}
  }
}