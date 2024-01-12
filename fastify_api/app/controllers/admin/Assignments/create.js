const { default: S } = require("fluent-json-schema");
const Assignment = require("../../../helpers/standardObjectSchemas/Assignment");

module.exports = {

  schema: {
    body: S.object().additionalProperties(false)
      .required(['exam_id'])
      .extend(Assignment),

      response: {
        200: S.object().additionalProperties(false).prop('id', S.string())
      }
  },
  
  async handler(req, res) {
    try {
      let {body} = req;
      const {ObjectId, db} = this.mongo;
  
      const exam_id = new ObjectId(body.exam_id);
      const user_id = new ObjectId(req.user.admin_id || req.user.uuid);
      
      switch(body.assignee.assign_to) {
        case "singleMember":
        let member_id = new ObjectId(body.assignee.assignee_id);
        
        if(! await db.collection('userMembers').findOne({member_id, user_id}))
          return res.status(400).send(`Unable to find the particular member.`);
        body.assignee.assignee_id = member_id;
        break;
        case "selectedMembers":
        const memberIds = body.assignee.assignee_id.map(id => new ObjectId(id));
        const selectedMembers = await db.collection('userMembers').find({user_id, member_id: {$in: memberIds}}).toArray();
        if(selectedMembers.length != memberIds)
          return res.status(400).send(`Only ${selectedMembers.length} members only valid.`);
        body.assignee.assignee_id = memberIds;
        break;
        case "singleGroup":
        const _id = new ObjectId(body.assignee.assignee_id);
        if(! await db.collection('groups').findOne({user_id, _id}))
          return res.status(400).send(`Unable to verify the selected group.`);
        body.assignee.assignee_id = _id;
        break;
        case "selectedGroups":
        const groupIds = body.assignee.assignee_id.map(id => new ObjectId(id));
        const selectedGroups = await db.collection('groups').find({user_id, _id: {$in: groupIds}}).toArray();
        
        if(groupIds.length != selectedGroups.length)
          return res.status(400).send(`Some are of the groups that you have selected are not available or you do not own them.` );
        body.assignee.assignee_id = groupIds;
        break;
        
        
      }
      
      const exam =  await db.collection('exams').findOne({user_id, _id: exam_id});
      if(!exam)
        return res.status(400).send(`The particular exam do not exists.`);
  
      body.exam_id = exam_id;
      body.user_id = user_id;
      body.created_at = Date.now();
      if(body.introduction.name.trim() == '')
        body.introduction.name = exam.title;
      
      
      const result = await db.collection('assignments').insertOne(body);
      return {id: result.insertedId};
      
    } catch(error) {
      return res.send(error);
    }
  }
  
}