import { ObjectId } from "mongodb";

export default interface CorrectionObject {
  id: ObjectId;
  type: string;
  correctAnswer: any;
  givenAnswer: any;
  fraction?: number;
  givenPoint: number;
  mpoint: number;
  settings?: any;

}