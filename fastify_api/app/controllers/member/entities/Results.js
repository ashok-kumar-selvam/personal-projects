const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    response: {
      200: S.array().items(
        S.object()
        .prop('id', S.string())
        .prop('title', S.string())
        .prop('admin', S.string())
        .prop('attempt', S.number())
        .prop('date', S.string())
      )
    }
  },
  
  async handler(req, res) {
    try {
      const {db, ObjectId} = this.mongo;
      const member_id = new ObjectId(req.user.uuid);
      const now = Math.floor(Date.now()/1000);
      const results = await db.collection('results').aggregate([
        { $lookup: {
          from: 'assignments',
          localField: 'assign_id',
          foreignField: '_id',
          as: 'assignment'
        }},
        { $unwind: '$assignment'},
        { $lookup: {
          from: 'users',
          localField: 'member_id',
          foreignField: '_id',
          as: 'member'
        }},
        { $unwind: '$member'},
        { $lookup: {
          from: 'attempts',
          localField: 'attempt_id',
          foreignField: '_id',
          as: 'attempt'
        }},
        { $unwind: '$attempt'},
        { $lookup: {
          from: 'users',
          localField: 'admin_id',
          foreignField: '_id',
          as: 'admin'
        }}, 
        { $unwind: '$admin'},
        { $match: {
          member_id: member_id,
          $or: [
            {'assignment.result.method': 'immediate'},
            {'assignment.result.method': 'scheduled', 'assignment.result.publishOn': {$lte: now}},
          ]
        }},
        { $project: {
          id: '$_id', title: '$assignment.introduction.name', attempt: '$attempt.attempt', admin: {$ifNull: ['$admin.name', '$admin.first_name']}, date: 1,
        }}
      ]).sort({date: -1}).toArray();
      return results;
    } catch(error) {
      return res.send(error);
    }
  }
}