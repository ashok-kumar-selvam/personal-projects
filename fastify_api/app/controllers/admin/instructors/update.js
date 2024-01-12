const { default: S } = require("fluent-json-schema");
const Instructor = require("../../../helpers/standardObjectSchemas/Instructor");

module.exports = {

  schema: {
    params: S.object().additionalProperties(false).prop('instructor_id', S.string().required()),
    body: Instructor,
    response: {
      200: S.object().prop('id', S.string())
    }

  },
  
  async handler(req, res) {
    try {
      const {body} = req;
      const _id = new this.mongo.ObjectId(req.params.instructor_id);
      const instructor = this.mongo.db.collection('users').findOne({_id, type: 'instructor'});
      
      const result = await this.mongo.db.collection('users').updateOne({_id}, {$set: {...instructor, ...body}});
      return {id: _id};
    } catch(error) {
      if(error.code === 11000) {
        const field = Object.keys(error.keyValue)[0];
        const value = error.keyValue[field];
        return res.status(400).send(`The ${field} field with value ${value} 
        is already registered. Please try again with different value`);
      }
      if(error.code == 500) {
        console.log(error);
        return res.status(500).send('An server error occured. Please try again or contact the admin');
      }
      
      return res.send(error);
    }
  }
}