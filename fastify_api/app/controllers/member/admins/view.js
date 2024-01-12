const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    response: {
      200: S.object()
      .prop('teachers', S.array().items(
        S.object()
        .prop('id', S.string())
        .prop('name', S.string())
      ))
    }
  }
  async handler(req, res) {
    try {
      const {db, ObjectId} = this.mongo;
      const member_id = new ObjectId(req.user.uuid);
      const teachers = await db.collection('userMembers').aggregate([
        { $lookup: {
          from: 'users',
          localField: 'user_id',
          foreignField: '_id',
          as: 'admin'
        }},
        { $unwind: '$admin'},
        { $match: {member_id}},
        { $project: {
          name: {$ifNull: ['$admin.name', '$admin.first_name']},
          id: '$_id',
        }}
      ]).toArray();
      return res.send({teachers});
    } catch(error) {
      return res.send(error);
    }
  }
}