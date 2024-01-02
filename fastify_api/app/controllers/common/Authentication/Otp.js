const {getKey} = require('../../../helpers/dbHelper');
const bcrypt = require('bcrypt');
const S = require('fluent-json-schema');

exports.forgot = {
  schema: {
    body: S.object().additionalProperties(false)
    .prop('email', S.string().format(S.FORMATS.EMAIL).required()),

    response: {
      200: S.string()
    }
  },

  async handler(req, res) {
    try {
      const email = req.body.email;
      const user = await this.mongo.db.collection('users').findOne({email});
      if(!user)
        return res.status(404).send('The email is not registered. ');

      const key = await getKey(this.mongo.db.collection('keys'), user.email, (24*86400)); 
      const mailOption = {
        to: user.email,
        key: key,
        subject: "Received request to change your password",
        template: 'forgot',
        name: `${user.first_name} ${user.last_name}`,
      
      };

      if(await this.sendMail(mailOption))
        return 'success';
      return res.status(400).send(`We are unable to send the verification email 
      at the moment. Please try to login again with   the credentials you provided 
      after some time.`);
    } catch(error) {
      return res.status(400).send(error.message);
    }
  }
}

exports.otp = {
  schema: {
    body: S.object()
    .additionalProperties(false)
    .prop('otp', S.number().required())
    .prop('email', S.string().format(S.FORMATS.EMAIL).required()),

    response: {
      200: S.string()
    }
  },

  async handler(req, res) {
    try {
      const data = req.body;
      const key = await this.mongo.db.collection('keys').findOne({
        to:  data.email,
        key: data.otp,
      });
      if(!key)
        return res.status(400).send('Invalid OTP');
      
      if(key.expire < Date.now)
        return res.status(400).send('The key expired');
      
      return 'success';
      
    } catch(error) {
      return res.status(400).send(error.message);
    }
  }
}

exports.recentOtp = async function(req, res) {
  try {
    const email = req.query.email;
    const keys = this.mongo.db.collection('keys');
    const key = await keys.findOne({to: email});
    if(!key)
    return res.status(400).send('Unable to find the key');

    const user = await this.mongo.db.collection('users').findOne({email});
    if(!user)
      return res.status(400).send('Invalid user email');
    
    return res.send({
      key: key.key,
      user_id: user._id});
  } catch(error) {
    
  }
}
