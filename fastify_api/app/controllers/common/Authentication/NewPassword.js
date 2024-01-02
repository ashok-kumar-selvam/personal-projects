const bcrypt = require('bcrypt');
const S = require('fluent-json-schema');


module.exports = {
  schema: {
    body: S.object()
    .additionalProperties(false)
    .prop('otp', S.number().required())
    .prop('email', S.string().format(S.FORMATS.EMAIL).required())
    .prop('new_password', S.string().required())
    .prop('confirm_password', S.string().required()),

    response: {
      200: S.string()
    }
  },

  async handler(req, res) {
    try {
      const data = req.body;
      const users = this.mongo.db.collection('users');
      const keys = this.mongo.db.collection('keys');
      const key = await keys.findOne({
        to: data.email,
        key: data.otp,
      });
      
      if(!key)
        return res.status(404).send('The otp is invalid.');
      
      if(key.expire < Date.now())
        return res.status(400).send('The oTP expired.');
      
      const user = await users.findOne({email: data.email});
      if(!user)
        return res.status(404).send('Invalid email/user.');
      
      if(await bcrypt.compare(data.new_password, user.password))
        return res.status(400).send('You are providing the old password.');
      
      if(data.new_password != data.confirm_password)
        return res.status(400).send('The new password and compare password do not match.');
      
      const password = await bcrypt.hash(data.new_password, 10);
      
      await users.updateOne(
      {email: user.email},
        {$set: {password}},
      );
      
      await keys.deleteOne({key: data.otp});
      await this.sendMail({
        to: user.email,
        subject: 'Your password was changed',
        template: 'passwordChanged',
        name: `${user.first_name} ${user.last_name}`,
      });
      return 'success';
    } catch(error) {
      return res.status(400).send(error.message);
    }
  }
}