const { default: S } = require("fluent-json-schema");
const Base = require("../../../config/Base");


module.exports = {

  schema: {
    body: S.object()
    .additionalProperties(false)
    .prop('type', S.string().const(Base.getUserType('type4')).required())
    .prop('name', S.string().required()),

    response: {
      200: S.object().prop('token', S.string())
    }
  },

  async handler(req, res) {
  try {
    const {body} = req;
    const Users = this.mongo.db.collection('users');
    const {insertedId} = await Users.insertOne(body);
    const token = this.jwt.sign({
      uuid: insertedId,
      ...body
    });
    return res.send({token});

  } catch(error) {
    console.error(error);
    return res.status(400).send('error occured');
  }
}
}