import { ObjectId } from "mongodb";

export interface UpdateData {
  point: number;
  is_correct: 'yes' | 'no' | 'partial' | 'review';
  has_answered: 'yes' | 'no';
}

export default interface UpdateObject {
  updateOne: {
    filter: {_id: ObjectId},
    update: {$set: UpdateData}
  }
}