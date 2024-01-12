const {default: S} = require('fluent-json-schema')

module.exports = {

  schema: {
    response: {
      200: S.object().additionalProperties(false)
      .prop('exams', S.array().items(
        S.object()
        .prop('id', S.string())
        .prop('title', S.string())
        .prop('owner', S.string())
        .prop('attempts', S.number())
        .prop('start', S.string())
        .prop('end', S.string())

      ))
    }
  },

  async handler(req, res) {
  try {
    const member_id = new this.mongo.ObjectId(req.user.uuid);
    
    const assign_to_query = [
    {'assignee.assign_to': 'singleMember',
        'assignee.assignee_id': member_id,
        },
        { 'assignee.assign_to': 'allMembers',
        'userMembers.member_id': member_id,
        'userMembers.status': 'approved'},
        {'assignee.assign_to': 'singleGroup',
        'groups.members': member_id,
        'userMembers.member_id': member_id,
        'userMembers.status': 'approved'},
        
    ];
    
    const exams = await this.mongo.db.collection('assignments').aggregate([
      { $lookup: {
        from: 'userMembers',
        localField: 'user_id',
        foreignField: 'user_id',
        as: 'userMembers'
      }},
      
      { $lookup: {
        from: 'groups',
        localField: 'assignee.assignee_id',
        foreignField: '_id',
        as: 'groups'
      }},
      
      { $lookup: {
        from: 'users',
        localField: 'user_id',
        foreignField: '_id',
        as: 'admin'
      }},
      { $unwind: '$admin'},
      { $match: {
        status: 'active',
        'time.start': {$lte: Math.floor(Date.now()/1000)},
        $and: [
        {$or: assign_to_query},
        {$or: [{'time.end': 0},
          {'time.end': {$gt: Math.floor(Date.now()/1000)}}],
          }]
      }},
      { $project: {
        title: '$introduction.name', id: '$_id', _id: 0,
        end: '$time.end', start: '$time.start', attempts: '$general.attempts',
        owner: {$ifNull: ['$admin.name', '$admin.first_name']},
      }}
      ]).toArray();
      return {exams};
  
  } catch(error) {
    return res.send(error);
  }
}
}