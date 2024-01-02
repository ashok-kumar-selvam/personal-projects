const bcrypt = require('bcrypt');
const S = require('fluent-json-schema');

module.exports = {

  schema: {
    body: S.object()
    .additionalProperties(false)
    .prop('username', S.string().required())
    .prop('password', S.string().required()),

    response: {

      200: S.object()
      .prop('id', S.string())
      .prop('name', S.string())
      .prop('type', S.string())
      .prop('isAuthed', S.boolean())
      .prop('status', S.string())
      .prop('token', S.string())
    }
  },

  async handler(req, res) {
    try {
      const data = req.body;
      
      const users = this.mongo.db.collection('users');
      
      const user = await users.findOne({email: data.username, type: {$in: ['admin', 'member']}}) ||
      await users.findOne({userId: data.username, type: 'instructor'});
      
      if(!user)
        return res.status(404).send(`Unable to find the account with the provided username.`);
      
      
      if(!await bcrypt.compare(data.password, user.password))
        return res.status(400).send('Wrong password!');
      
      const payload = {
        uuid: user._id,
        email: user.email,
        type: user.type,
        status: user.status,
        admin_id: user.admin_id,
        name: user.name || `${user.first_name} ${user.last_name}`,
      };
      const token = await this.jwt.sign(payload);
      return {
        id: user._id,
        name: user.name || `${user.first_name} ${user.last_name}`,
        type: user.type,
        status: user.status,
        isAuthed: true,
        token: token,
      };
    } catch(error) {
      console.error(error);
      return res.status(400).send(error.message);
    }
  }
}