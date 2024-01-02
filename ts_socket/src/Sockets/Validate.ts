import Socket from '../Interfaces/Socket';
import AssignmentValidator from '../Utils/AssignmentValidator';

export default async function(socket: Socket) {
  socket.on('validate', async (assign_id: string) => {
    try {
      if(!socket?.mongo || !socket?.mongo?.ObjectId || !socket?.mongo?.db)
        return socket.emit('error', 'Some error in configuration and unable to connect to database.');
      
      const {db, ObjectId} = socket.mongo;
      const validator = new AssignmentValidator(assign_id, db, socket.user )
      if(! await validator.validate())
        return socket.emit('error', validator.getError());

      socket.isValidated = true;
      socket.isResumable = validator.isResumable;
      socket.resumableOnly = validator.resumableOnly;

      const assign = validator.assign;


      socket.validatedData = {
        id: assign._id,
        name: assign.introduction.name,
        instructions: assign.introduction.instructions,
        attempt: validator.attemptedCount+1,
        attempts: assign.general.attempts,
        questions: validator.questionCount,
        passmark: assign.general.passmark,
        duration: assign.time.duration,
        mpoint: assign.question.mpoint,
        questionTime: assign.question.time,
        end: assign.time.end,
        resumable: assign.general.resumable,
      };
      

      const reply = validator.resumableOnly ? 'resumableOnly': validator.isResumable ? 'resumable': 'fresh';
      return socket.emit('validated', reply);

    } catch(error: any) {
      console.error(error);
      socket.emit('error', 'unexpected error occured');
    }
  })
}