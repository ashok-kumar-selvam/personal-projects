exports.verify = async function(req, res) {
  try {
    const code = req.params.admin_code;
    const shareCode = await this.mongo.db.collection('shareCodes').findOne({code: Number(code)});
    
    
    if(!shareCode)
      return res.status(404).send(`Unable to find the code details.`);
    
    if(shareCode.expiresOn > 0 && shareCode.expiresOn < Math.floor(Date.now()/1000))
      return res.status(400).send('The invite code has expired. Please request a new code from the admin.');
    
    const admin = await this.mongo.db.collection('users').findOne({_id: new this.mongo.ObjectId(shareCode.admin_id)});
    
    if(!admin)
      return res.status(404).send(`Unable to find the admin.`);
    
    return {name: admin.first_name+' '+admin.last_name, code};
  } catch(error) {
    return res.send(error);
  }
}

exports.request = async function(req, res) {
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