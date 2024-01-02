import { ObjectId } from "mongodb";

type assignmentValidationErrorActions = 'reject' | 'error' | 'login' | 'register' |  'notfound' | 'nonext' | 'noprevious';
type approvedUserTypesForAssignment = 'admin' | 'member' | 'temporary';
type answerIdOrNumber = {
  number?: number;
  answer_id?: ObjectId;
};

export {assignmentValidationErrorActions, approvedUserTypesForAssignment, answerIdOrNumber}