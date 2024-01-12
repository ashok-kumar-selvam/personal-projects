const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false)
      .prop('assign_id', S.string().required())
      .prop('segment', S.string().enum(['introduction', 'question', 'time', 'assignee', 'general', 'result']).required()),

      response: {
        200: S.object()
      }
  },
  
  async handler(req, res) {
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
}