exports.create = async function(req, res) {
  try {
    const data = {
      user_id: new this.mongo.ObjectId(req.user.admin_id || req.user.uuid),
      created_at: Date.now(),
      members: [],
      ...req.body
    };
    const result = await this.mongo.db.collection('groups').insertOne(data);
    return {id: result.insertedId};
  } catch(error) {
    return res.send(error);
  }
}

exports.update = async function(req, res) {
  try {
    const result = await this.mongo.db.collection('groups').updateOne(
    {_id: new this.mongo.ObjectId(req.params.group_id)},
    {$set: req.body});
    if(result.modifiedCount != 1)
      return res.status(400).send(`Unable to update the group details.`);
    return {id: req.params.group_id};
  } catch(error) {
    return res.send(error);
  }
}

exports.show = async function(req, res) {
  try {
    const group_id = new this.mongo.ObjectId(req.params.group_id);
    const group = await this.mongo.db.collection('groups').findOne({_id: group_id}, {
      projection: {
        _id: 0, id: '$_id', name: 1, description: 1, members: 1
      }
    });
    const members = await this.mongo.db.collection('users').aggregate([
    { $lookup: {
      from: 'userMembers',
      localField: '_id',
      foreignField: 'member_id',
      as: 'userMembers'
    }},
    { $match: {
      _id: {$in: group.members},
      'userMembers.status': 'approved',
      'userMembers.user_id': new this.mongo.ObjectId(req.user.admin_id || req.user.uuid),
    }},
    
    { $project: {
      _id: 0, id: '$_id', email: 1,
      name: {$concat: ['$first_name', ' ', '$last_name']},
    }},
    ]).toArray();
    return {group, members};
  } catch(error) {
    return res.send(error);
  }
}

exports.list = async function(req, res) {
  try {
    const groups = await this.mongo.db.collection('groups').aggregate([
    
    { $match: {
      user_id: new this.mongo.ObjectId(req.user.admin_id || req.user.uuid)
    }},
    { $sort: {
      created_at: -1
    }},
    { $project: {
      _id: 0, id: '$_id', name: 1, description: 1,
      created_at: 1,
      total: {$cond: {
        if: {$isArray: "$members"},
        then: {$size: '$members'},
      else: 0}},
    }}]).toArray();
    return {groups};
  } catch(error) {
    return res.send(error);
  }
}

exports.delete = async function(req, res) {
  try {
    const _id = new this.mongo.ObjectId(req.params.group_id);
    
    const result = await this.mongo.db.collection('groups').deleteOne({_id});
    if(result.deletedCount != 1)
      return res.status(400).send(`Unable to delete.`);
    
    
    const result3 = await this.mongo.db.collection('assignments').deleteMany({assignee_id: _id});
    
    return 'success';
  } catch(error) {
    return res.send(error);
  }
}

exports.addMembers = async function(req, res) {
  try {
    const {body} = req;
    const group_id = new this.mongo.ObjectId(req.params.group_id);
    const members  = body.map(id => new this.mongo.ObjectId(id));
    
    const result = await this.mongo.db.collection('groups').updateOne({_id: group_id},
    {$addToSet: {members: {$each: members}}});
    
    if(result.modifiedCount != members.length)
      return res.status(400).send(`There was some error updating members`);
    
    return 'success';
    
  } catch(error) {
    return res.send(error);
  }
}

exports.removeMember = async function(req, res) {
  try {
    const member_id = new this.mongo.ObjectId(req.query.member_id);
    const group_id = new this.mongo.ObjectId(req.params.group_id);
    const result = await this.mongo.db.collection('groups').updateOne({_id: group_id},
      {$pull: {members: member_id}});
    return 'success';
    
  } catch(error) {
    return res.send(error);
  }
}

exports.nonmembers = async function(req, res) {
  try {
    const group_id = new this.mongo.ObjectId(req.params.group_id);
    const group = await this.mongo.db.collection('groups').findOne({_id: group_id});
    
    const nonmembers = await this.mongo.db.collection('users').aggregate([
    { $lookup: {
      from: 'userMembers',
      localField: '_id',
      foreignField: 'member_id',
      as: 'members'
    }},
    { $match: {
      'members.user_id': new this.mongo.ObjectId(req.user.admin_id || req.user.uuid),
      'members.status': 'approved',
      _id: {$nin: group.members},
    }},
    { $project: {
      _id: 0, id: '$_id', email: 1,
      name: {$concat: ['$first_name', ' ', '$last_name']}
    }}]).toArray();
    return nonmembers;
  } catch(error) {
    return res.send(error);
  }
}