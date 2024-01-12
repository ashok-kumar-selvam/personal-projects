const { default: S } = require("fluent-json-schema");

module.exports = {

  schema: {
    body: S.object().additionalProperties(false)
    .prop('first_name', S.string().required())
    .prop('email', S.string().format(S.FORMATS.EMAIL).required())
    .prop('mobile', S.string().required())
    .prop('password', S.string().required()),

    response: {
      200: S.object().prop('message', S.string())
    }
  },
  
  async handler(req, res) {
    try {
      const {body} = req;
      const users = this.mongo.db.collection('users');
      
      if(body.email && body.email != '' && await users.findOne({email: body.email}))
        return res.status(400).send(this.config.getMessage('emailAlreadyRegistered'));
      
      if(body.mobile && body.mobile != '' && await users.findOne({mobile: body.mobile}))
        return res.status(400).send(this.config.getMessage('mobileAlreadyRegistered'));
      
      const password = body.password && body.password != '' ? body.password: Math.random().toString(36).slice(-8);
      let data = {...body,
      password: await bcrypt.hash(password, 10),
      mode: 'created',
      createdBy: new this.mongo.ObjectId(req.user.admin_id || req.user.uuid),
      type: 'member',
      status: 'pending',
      expiresOn: Math.floor(Date.now()/1000)+86400,
      created_at: Date.now(),
      
      };
      
      const result = await users.insertOne(data);
      const mailOptions = {
        name: `${body.first_name} ${body.last_name}`,
        template: 'createMember',
        subject: `${req.user.name} has created an member account`,
        admin_id: req.user.admin_id || req.user.uuid,
        admin_name: req.user.name,
        admin_email: req.user.email,
        link: `${process.env.APP_URL}?id=${result.insertedId}&tpwd=${password}`,
        
      };
      
      if(this.sendMail(mailOptions))
        return {message: 'The member created and sent an email.'};
      return res.status(400).send(`Some error in sending email`);
    } catch(error) {
      return res.send(error);
    }
  }
}