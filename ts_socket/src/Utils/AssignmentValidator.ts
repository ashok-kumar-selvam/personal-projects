import { ObjectId, Db } from "mongodb";
import User from "../Interfaces/User";
import {approvedUserTypesForAssignment}from './Types';
import BaseValidator from "./BaseValidator";

class AssignmentValidator extends BaseValidator {
  private allowedUserTypes: approvedUserTypesForAssignment[]  = ['admin', 'member', 'temporary'];
  private assign_id: ObjectId;
  protected db: Db;
  protected user: User | undefined;
  protected member_id: ObjectId | undefined;
  public assign: any;
  public isResumable: boolean = false;
  public resumableOnly: boolean = false;
  public questionCount: number = 0;
  public attemptedCount: number = 0;
  


  constructor(assign_id: string,  db: Db, user: User | undefined) {
    super();
    this.assign_id = new ObjectId(assign_id);
    this.db = db;
    this.user = user;
    this.member_id = user ? new ObjectId(user.uuid): undefined;
  }

  async setAssignment(): Promise<boolean> {
    try {
      
      const _id = this.assign_id;
      const assignment = await this.db.collection('assignments').findOne({_id});
      if(!assignment)
        return this.setError('error', 'Unable to find the exam. ');

      if(assignment.status !== 'active')
        return this.setError('error', "The exam is not active. ");

      this.assign = assignment;
      return true;
    } catch(error: any) {
      console.error(error);
      return this.setError('error', 'Unexpected error');
    }
  }

  async checkUser(): Promise<boolean> {
    try {
      if(! await this.setAssignment())
        return false;

      const assign_to = this.assign.assignee.assign_to;
      if(!this.user)
        return this.setError((assign_to === 'anonymousUsers' ? 'register': 'login'), 'Please login or register to continue. ');

      this.member_id = new ObjectId(this.user.uuid);

      if(this.user.uuid == this.assign.user_id || this.user.admin_id == this.assign.user_id)
        return true;

      switch(this.assign.assignee.assign_to) {
        case "singleMember":
          if(this.member_id !== this.assign.assignee.assignee_id)
            return this.setError('reject', 'This exam was assigned to different user and you do not have permission to access this exam. ');
        break;
        case "allMembers":
          const isMember = await this.db.collection('userMembers').findOne({user_id: this.assign.user_id, member_id: this.member_id, status: 'approved'});
          if(!isMember)
            return this.setError('reject', 'This exam is only available for members of particular admin. Please contact the person who shared this exam to you to get more info. ');

        break;
        case "singleGroup":
          const isGroupMember = await this.db.collection('groups').findOne({_id: this.assign.assignee.assignee_id, members: this.member_id});
          if(!isGroupMember)
            return this.setError('reject', 'This exam is only for certain group of members. Please contact your teacher/admin for more info. ');
        break;
        case "anonymousUser":
          const user = await this.db.collection('users').findOne({_id: new ObjectId(this.user.uuid)});
          if(!user || !this.allowedUserTypes.includes(user.type))
            return this.setError('reject', 'Unable to verify the user type . ');

          if(Array.isArray(this.assign.assignee.anonymousEmails) && !this.assign.assignee.anonymousEmails.includes(user.email))
            return this.setError('reject', 'This exam is only for certain approved email ids. ');
        break;
        default:
          return this.setError('error', `Unknown user type ${this.assign.assignee.assign_to}`);
      }
      return true;
    } catch(error: any) {
      return this.setError('error', "Unexpected error occured. ");
    }
  }

  async checkTime(): Promise<boolean> {
    try {
      if(! await this.checkUser())
        return false;

      const currentTime = Math.floor(Date.now()/1000);
      if(this.assign.time.start > currentTime)
        return this.setError('reject', 'The exam did not start. Please try later ');

      if(this.assign.time.end > 0 && this.assign.time.end < currentTime)
        return this.setError('reject', 'The exam has ended. ');
      return true;
    } catch(error: any) {
      console.error(error);
      return this.setError('error', 'Unexpected error. ');
    }
  }

  async checkAttempt(): Promise<boolean> {
    try {
      if(! await this.checkTime())
        return false;

      const assign_id: ObjectId = this.assign._id;
      const member_id: ObjectId = new ObjectId(this.user?.uuid);
      const totalAttemptedMembers = await this.db.collection('attempts').distinct('member_id', {assign_id}); // The total unique members who attempted the exam
      const attemptedCount = await this.db.collection('attempts').countDocuments({assign_id, member_id}); // attempted count for this member on this exam
      const started = await this.db.collection('attempts').findOne({assign_id, member_id, status: 'started'}); // checking whether any existing attempt is pending

      this.attemptedCount = attemptedCount;
// if the exam is resumable and there is a pending exam
      if(this.assign.general.resumable === 'yes' && started) 
        this.isResumable = this.assign.general.attempts > 0 ? (attemptedCount <= this.assign.general.attempts): true; // even though the exam is resumable, if the allowed attempt is not 0 and the attempted count of the member exceeds the provided attempt count, it is false;

      // if the exam is resumable and if the member is attempting the last attempt
      if(this.isResumable && this.assign.general.attempts > 0 && attemptedCount === this.assign.general.attempts)
        this.resumableOnly = true;

      // general attempt test where the attempted count should not exceed the provided attempt count if the provided count is not 0.
      if(!this.isResumable && this.assign.general.attempts > 0 && attemptedCount >= this.assign.general.attempts)
        return this.setError('reject', 'Looks like you have exhausted all your attempts! Please try again or contact your admin/teacher. ');

      if(attemptedCount === 0 && this.assign.assignee.limit > 0 && totalAttemptedMembers.length >= this.assign.assignee.limit)
        return this.setError('reject', 'Maximum attempt limit is reached. ');


      return true;
    } catch(error: any) {
      console.error(error);
      return this.setError('error', 'Unexpected error. ')
    }
  }

  async checkQuestions(): Promise<boolean> {
    try {
      if(! await this.checkAttempt())
        return false;

      const questionCount = await this.db.collection('questionPaper').countDocuments({entity_id: new ObjectId(this.assign.exam_id)});
      if(questionCount <= 0)
        return this.setError('reject', 'The exam has no questions. Please contact the admin/teacher for more info. ');

      this.questionCount = questionCount;
      return true;
    } catch(error: any) {
      console.error(error);
      return this.setError('error', 'Unexpected error')
    }
  }

  async validate(): Promise<boolean> {
    try {
      if(! await this.checkQuestions())
        return false;
      return true;
    } catch(error: any) {
      console.error(error);
      return this.setError('error', 'Unexpected error')
    }
  }
}

export default AssignmentValidator;