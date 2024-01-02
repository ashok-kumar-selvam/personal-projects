exports.create = async function(req, res) {
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

exports.show = async function(req, res) {
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

exports.edit = async function(req, res) {
  try {
    const {db, ObjectId} = this.mongo;
    const assign_id = new ObjectId(req.params.assign_id);
    const segment = req.params.segment;
    const assignment = await db.collection('assignments').findOne({_id: assign_id});
    return assignment[segment];
  } catch(error) {
    return res.send(error);
  }

}

exports.update = async function(req, res) {
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


exports.list = async function(req, res) {
  try {
    const exam_id = new this.mongo.ObjectId(req.params.exam_id);
    const assignments = await this.mongo.db.collection('assignments').aggregate([
    { $lookup: {
      from: 'users',
      localField: 'assignee.assignee_id',
      foreignField: '_id',
      as: 'users'
      
    }},
    { $lookup: {
      from: 'groups',
      localField: 'assignee.assignee_id',
      foreignField: '_id',
      as: 'groups'
      
    }},
    
    { $match: {
      exam_id: exam_id
    }},
    
    { $project: {
      start: '$time.start', end: '$time.end',_id: 0, id: '$_id', name: '$introduction.name', status: 1,
      assign_to: {
        $cond: {
          if: {$eq: ['$assignee.assign_to', 'singleMember']},
          then: {$arrayElemAt: ['$users.first_name', 0]},
          else: {
            $cond: {
              if: {$eq: ['$assignee.assign_to', 'singleGroup']},
              then: {$arrayElemAt: ['$groups.name', 0]},
              else: '$assignee.assign_to'
            }
          }
        }
      }, 
      created_at: 1
    }}
    ]).sort({created_at: -1}).toArray();
    const exam = await this.mongo.db.collection('exams').findOne({_id: exam_id});
    return {title: exam.title, assignments};
  } catch(error) {
    return res.send(error);
  }
}

exports.activate = async function(req, res) {
  try {
    const _id = new this.mongo.ObjectId(req.params.assign_id);
    const {body} = req;
    const result = await this.mongo.db.collection('assignments').updateOne({_id}, {$set: {status: body.status}});
    return 'success';
    
  } catch(error) {
    return res.send(error);
  }
}

exports.publish = async function(req, res) {
  try {
    const _id = new this.mongo.ObjectId(req.params.assign_id);
    const {body} = req;
    const assignment = await this.mongo.db.collection('assignments').findOne({_id});
    const obj = {...assignment['result'], published: body.published};
    const result = await this.mongo.db.collection('assignments').updateOne({_id}, {$set: {result: obj}});
    return 'success';
    
  } catch(error) {
    return res.send(error);
  }
}