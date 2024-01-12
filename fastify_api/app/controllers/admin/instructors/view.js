const { default: S } = require("fluent-json-schema");
const Instructor = require("../../../helpers/standardObjectSchemas/Instructor");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('instructor_id', S.string().required()),
    response: {
      200: S.object()
      .prop('instructor', 
      S.object().prop('id', S.string())
      .extend(Instructor)
      )
    }
  },
  
  async handler(req, res) {
    try {
      const _id = new this.mongo.ObjectId(req.params.instructor_id);
      const instructor = await this.mongo.db.collection('users').findOne({_id, type: 'instructor'});
      delete instructor['password'];
      return {instructor: {...instructor, id: instructor._id}};
    } catch(error) {
      return res.send(error);
    }
  }
}