const S = require('fluent-json-schema');

module.exports = {

  schema: {

    body: S.object()
    .additionalProperties(false)
    .prop('user_id', S.string().required())
    .prop('key', S.number().required()),

    response: {

      200: S.object()
      .prop('uuid', S.string())
      .prop('id', S.string())
      .prop('email', S.string().format(S.FORMATS.EMAIL))
      .prop('type', S.string())
      .prop('status', S.string())
      .prop('token', S.string())
      .prop('isAuthed', S.string())
      .prop('hasPlan', S.string())
    }
  },

  async handler(req, res) {
    try {
      const data = req.body;
      const keys = this.mongo.db.collection('keys');
      const users = this.mongo.db.collection('users');
      const user = await users.findOne({_id: new this.mongo.ObjectId(data.user_id)});
      if(!user)
        return res.status(400).send('Unable to find the user.');
      
      if(user.status == 'approved')
        return res.status(400).send('The user is already verified. Please login to continue.');
      const key = await keys.findOne({key: data.key});
      if(!key)
        return res.status(400).send('Unable to find the key');
      
      if(key.to != user.email)
        return res.status(400).send('Invalid request');
      
      if(key.expire <= Date.now())
        return res.status(400).send('The key has expired. Please generate new key.');
      
      await users.updateOne(
        {email: user.email},
        {$set: {status: 'approved'}}
      );
      
      await keys.deleteOne({key: data.key});
      
      const payload = {
        uuid: user._id,
        name: `${user.first_name} ${user.last_name}`,
        email: user.email,
        type: user.type,
        status: 'approved',
      }
      
      const token = await this.jwt.sign(payload);
      
      return {...payload, token,
        isAuthed: true,
        id: payload.uuid,
        has_plan: 'yes',
      }
    } catch(error) {
      return res.status(400).send(error.message);
    }
  }
}