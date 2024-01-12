const { default: S } = require("fluent-json-schema");
const Assignment = require("../../../helpers/standardObjectSchemas/Assignment");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false)
      .prop('assign_id', S.string().required())
      .prop('segment', S.string().enum(['introduction', 'assignee', 'question', 'time', 'general', 'result']).required()),
      body: Assignment.only(['introduction', 'assignee', 'question', 'time', 'general', 'result']),
    response: {
        200: S.object().prop('id', S.string())
    }
  },
  
  async handler(req, res) {
    try {
      const _id = new this.mongo.ObjectId(req.params.assign_id);
      const segment = req.params.segment;
      let {body} = req;
      const {db, ObjectId} = this.mongo;
      const user_id = new ObjectId(req.user.admin_id || req.user.uuid);
      
      
      const assignment = await this.mongo.db.collection('assignments').findOne({_id});
      
      if(segment == 'assignee') {
            
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
  
  
      }
      const updater = {...assignment[segment], ...body[segment]};
      
      const result = await this.mongo.db.collection('assignments').updateOne(
      {_id},
      {$set: {[segment]: updater}});
      return {id: _id};
    } catch(error) {
      return res.send(error);
    }
  } 
  
}