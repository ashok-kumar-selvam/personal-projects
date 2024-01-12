const { default: S } = require("fluent-json-schema");
const Instructor = require("../../../helpers/standardObjectSchemas/Instructor");
const getCredentials = require('../../../helpers/getCredentials');
const bcrypt = require('bcrypt');

module.exports = {

  schema: {
    body: S.object().additionalProperties(false)
    .required(['name', 'email'])
    .extend(Instructor),

    response: {
      200: S.object().additionalProperties(false)
        .prop('user_id', S.string())
        .prop('password', S.string())
    }
  },

  async handler(req, res) {
    try {
      const {body} = req;
      const name = body.name.replace(' ', '').trim();
      if(name.length < 4)
        return res.status(400).send(`The name should be 4 in length without any spaces.`);
      let {userId, password} = await getCredentials(name, this.mongo.db.collection('users'));
      
      if(await this.mongo.db.collection('users').findOne({email: body.email}))
        return res.status(400).send(`This email id is already registered with an other account.`);
      
      let data = {...body, userId,  
      password: await bcrypt.hash(password, 10),
      admin_id: new this.mongo.ObjectId(req.user.uuid),
      type:  'instructor',
      status: 'active',
      created_at: Date.now()
      };
      
      const result = await this.mongo.db.collection('users').insertOne(data);
      
      
      return {user_id: userId, password};
    } catch(error) {
      return res.send(error);
    }
  }
}