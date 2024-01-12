const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    body: S.object().additionalProperties(false)
    .prop('code', S.string()),

    response: {
      200: S.string().const('success')
    }
  },
  
  async handler(req, res) {
    try {
      const code = Number(req.body.code);
      const shareCode = await this.mongo.db.collection('shareCodes').findOne({code});
      
      if(!shareCode)
        return res.status(400).send('The code does not exists.');
      
      if(shareCode.expiresOn > 0 && shareCode.expiresOn < Math.floor(Date.now()/1000))
        return res.status(400).send(`The code has expired. Please request a new code from the admin.`);
      
      const result = await this.mongo.db.collection('userMembers').insertOne({
        user_id: new this.mongo.ObjectId(shareCode.admin_id),
        member_id: new this.mongo.ObjectId(req.user.uuid),
        created_at: Date.now(),
        status: 'pending',
      });
      return 'success';
      
    } catch(error) {
      return res.send(error);
    }
  } 
}