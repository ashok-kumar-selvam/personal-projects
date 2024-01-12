const { default: S } = require("fluent-json-schema");
const Assignment = require("../../../helpers/standardObjectSchemas/Assignment");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('assign_id', S.string().required()),
    response: {
      200: S.object().additionalProperties(false)
        .prop('assignment', S.object().additionalProperties(false)
          .prop('id', S.string())
          .prop('title', S.string())
          .extend(Assignment)
        )
        .prop('options', S.object().additionalProperties(false)
          .prop('attempts', S.number().default(0))
          .prop('users', S.number().default(0))
          .prop('results', S.number().default(0))
          .prop('status', S.string())
        )
    }
  },

  async handler(req, res) {
    try {
      const {db, ObjectId} = this.mongo;
      
      const assign_id = new ObjectId(req.params.assign_id);
      let assignment = await db.collection('assignments').findOne(
      {_id: assign_id},
      { projection: {_id: 0, id: '$_id', status: 1, exam_id: 1, introduction: 1, assignee: 1,  question: 1, time: 1, general: 1, result: 1,}}); 
      const exam = await db.collection('exams').findOne({_id: new ObjectId(assignment.exam_id)});
      assignment.title = exam.title || 'unknown';
      const {assignee_id} = assignment.assignee;
  
      switch(assignment.assignee.assign_to) {
        case "singleMember":
        const member = await db.collection('users').findOne({_id: new ObjectId(assignee_id)});
        assignment.assignee.assign_to = `${member.first_name} ${member.last_name}`;
        break;
        case "singleGroup":
        const group = await db.collection('groups').findOne({_id: new ObjectId(assignee_id)});
        assignment.assignee.assign_to = group.name;
        break;
        
      }
      
      
      delete assignment['assignee']['assignee_id'];
      
      
        const options = {
          attempts: 0,
          users: 0,
          status: 'active',
          results: 0
        };
        return {assignment,  options};
        
    } catch(error) {
      return res.send(error);
    }
  }
}