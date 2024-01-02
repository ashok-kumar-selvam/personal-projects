import { ObjectId } from "mongodb";

export default interface Question {
  id: ObjectId;
  attempt_id: ObjectId;
  question_id: ObjectId;
  number: number;
  type: string;
  question: string;
  options?: any[];
  answer: any;
  time: number;
  has_previous: 'yes' | 'no';
  has_next: 'yes' | 'no';
  has_attempted: 'yes' | 'no';
  has_marked: 'yes' | 'no';
  given_point: number;
  choices?: any[] | boolean;
  settings?: any;




}