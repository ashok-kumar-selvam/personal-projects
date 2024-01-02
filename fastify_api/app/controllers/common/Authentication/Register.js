const bcrypt = require('bcrypt');
const {getKey} = require('../../../helpers/dbHelper');
const S = require('fluent-json-schema');

/**
 * This module handles the registration process initiated through the register page and the /register route.
 * The types can be either admin or member.
 */

module.exports = {
  /**
   * The schema holds the request and response data along with the types.
   * 
   */
  schema: {
    //Expected body
    body: S.object()
    .additionalProperties(false)
    .prop('type', S.string().enum(['admin', 'member']).required())
    .prop('name', S.string().required())
    .prop('email', S.string().format(S.FORMATS.EMAIL).required())
    .prop('mobile', S.string().minLength(10).maxLength(10).required())
    .prop('password', S.string().minLength(8).required())
    .prop('confirm_password', S.string().required())
    .prop('status', S.string().default('pending').const('pending'))
    .prop('referal', S.string()),

    // returning value
    response: {
      200: S.object().prop('id', S.string())
    }
  },

  async handler(req, res) {
    try {
      const users = this.mongo.db.collection('users');
      const keys = this.mongo.db.collection('keys');
      let data = req.body;

      if(await users.findOne({email: data.email}))
        return res.status(409).send('The email id is already registered. Please try with some other email id');

      if(await users.findOne({mobile: data.mobile}))
        return res.status(409).send('The mobile number is already registered. Please try with some other mobile number. ');

      
      if(data.password != data.confirm_password)
        return res.status(400).send('The passwords do not match');
  
      data = {
        name: data.name,
        first_name: data.name,
        last_name: "",
        email: data.email,
        mobile: data.mobile,
        referer: data.referer,
        password: await bcrypt.hash(data.password, 10),
        status: 'pending',
        type: data.type,
        created_at: Date.now(),
      };
      
      
      const result = await users.insertOne(data);
      const key = await getKey(keys, data.email, (24*86400)); 
      const mailOption = {
        to: data.email,
        key: key,
        subject: "Please verify whether it is you",
        template: 'verify',
        name: `${data.first_name} ${data.last_name}`,
        
      };
  
      if(await this.sendMail(mailOption))
        return {id: result.insertedId};
      return res.status(400).send(`We are unable to send the verification email 
      at the moment. Please try to login again with   the credentials you provided 
      after some time.`);
      
    } catch(error) {
      if(error.code === 11000) {
        const field = Object.keys(error.keyValue)[0];
        const value = error.keyValue[field];
        console.log('The error occured ');
        return res.status(400).send(`The ${field} field with value ${value} 
        is already registered. Please try again with different value`);
      }
      if(error.code == 500) {
        console.log(error);
        return res.status(500).send('An server error occured. Please try again or contact the admin');
      }
      return res.status(400).send(error.message);
    }
  }
}