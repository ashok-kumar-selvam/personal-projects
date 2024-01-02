import { ObjectId } from "mongodb";


export default interface Answer {
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