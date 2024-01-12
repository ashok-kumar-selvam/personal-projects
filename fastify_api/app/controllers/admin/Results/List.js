const { default: S } = require("fluent-json-schema");
const Paginator = require('../../../helpers/Paginator');
module.exports = {
  schema: {
    query: S.object().additionalProperties(false)
    .prop('page', S.number().required())
    .prop('sortby', S.string().enum(['nameAscending', 'nameDescending', 'dateOlder', 'dateNewer']).default('dateNewer')),
    params: S.object().additionalProperties(false)
    .prop('assign_id', S.string().required())

  },

  async handler(req, res) {
    try {
      const {db, ObjectId} = this.mongo;
      const {page, sortby} = req.query;
      const sortField = sortby.startsWith('name') ? 'name': sortby.startsWith('date') ? 'date': '_id';
      const sortValue = sortby.includes('Newer') || sortby.includes('Descending') ? -1: 1;
      const sort = {[sortField]: sortValue};
      console.log(sort);
      const assign_id = new ObjectId(req.params.assign_id);
      const results = await db.collection('attempts').aggregate([
        { $lookup: {
          from: 'users',
          localField: 'member_id',
          foreignField: '_id',
          as: 'user'
        }},
        { $unwind: '$user'},
        { $match: {assign_id}},
        { $project: {
          attempt: 1, name: '$user.first_name', status: 1, _id: 0, id: '$_id', date: 1,
        }}
      ]).sort({[sortField]: sortValue}).toArray();
      const paginator = new Paginator(results);
      const items = paginator.paginate(page);
      return res.send({
        hasPrevious: paginator.hasPrevious(page),
        hasNext: paginator.hasNext(page),
        page: page,
        last: paginator.totalPages,
        items: items,
      })
    } catch(error) {
      return res.send(error);
    }
  }
}