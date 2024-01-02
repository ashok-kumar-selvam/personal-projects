const bcrypt = require('bcrypt');
const xlsx = require('xlsx');

const sanitize = (array) => {
  for(let i = array.length-1; i > 0; i--) {
    if(array[i] === null) {
      delete array[i];
      continue;
    }
    return array;
  }
  return array;
}

exports.create = async function(req, res) {
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

exports.code = async function(req, res) {
  try {
    const {query} = req;
    let code;
    while(true) {
      code = Math.floor(Math.random()*89999999)+10000000;
      if(!await this.mongo.db.collection('shareCodes').findOne({code}))
        break;
      
    }
    const result = await this.mongo.db.collection('shareCodes').insertOne({
      admin_id: new this.mongo.ObjectId(req.user.admin_id || req.user.uuid),
      code: code,
      expiresOn: query.expiresOn,
      limit: query.limit,
      
    });
    
    return {code, name: req.user.name};
    
  } catch(error) {
    return res.send(error);
  }
}

/**  The request to /admin/members/:id will be handled by this function
 * the request must contain the property "status" with the value either "approved" or "suspended".
 */
exports.update = async function(req, res) {
  try {
    const member_id = new this.mongo.ObjectId(req.params.member_id);
    const user_id = new this.mongo.ObjectId(req.user.admin_id || req.user.uuid);
    const {status} = req.body;
    
    const result = await this.mongo.db.collection('userMembers').updateOne({member_id, user_id}, {$set: { status}});
    if(result.modifiedCount != 1)
      return res.status(400).send(`Unable to update the member details.`);
    return 'success';
  } catch(error) {
    return res.send(error);
  }
}

exports.list = async function(req, res) {
  try {
    const members = await this.mongo.db.collection('users').aggregate([
    { $lookup: {
      from: 'userMembers',
      localField: '_id',
      foreignField: 'member_id',
      as: 'member'
    }},
    
    { $match: {
      'member.user_id': new this.mongo.ObjectId(req.user.admin_id || req.user.uuid),
      
    }},
    { $project: {
      first_name: 1, last_name: 1,   email: 1, status: '$member.status', member_id: '$member.member_id', 
      created_at: '$member.created_at', name: {$concat: ['$first_name', ' ', '$last_name']}
    }}
    ]).toArray();
    
    
    const stats = {
      total: members.length,
      approved: members.reduce((number, member) => member.status == 'approved' ? number+1: number, 0),
      pending: members.reduce((number, member) => member.status == 'pending' ? number+1: number, 0),
    };
    return {members, stats};
  } catch(error) {
    return res.send(error);
  }
}

exports.delete = async function(req, res) {
  try {
    const user_id = new this.mongo.ObjectId(req.user.admin_id || req.user.uuid);
    const member_id = new this.mongo.ObjectId(req.params.member_id);
    
    const groups = await this.mongo.db.collection('groups').updateMany({user_id}, {$pull: {members: member_id}});
    const assignments = this.mongo.db.collection('assignments').deleteMany({user_id, 'assignee.assignee_id': member_id});
    const result = this.mongo.db.collection('userMembers').deleteOne({user_id, member_id});
    return 'success';
    
  } catch(error) {
    return res.send(error);
  }
}

exports.upload = async function(req, res) {
  try {
    const {body} = req;
    if(body.members.mimetype != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
      return res.status(400).send(`Please only send xlsx file to upload.`);

    const workbook = xlsx.read(body.members.data);
    const worksheet = workbook.Sheets['Sheet1'];
    let members = xlsx.utils.sheet_to_json(worksheet, {header: 1});
    const exclude = members.shift();
    let newMembers = [];
    
    for(let [index, member] of members.entries()) {
      
      member = await sanitize(member);
      
      
      if(member.length < 3)
        continue;
      const number = index+1;
      
      if(member.length > 4)
        return res.status(400).send(`member ${number}: You can only provide 4 values in a row.`);
      
      let [first_name, last_name, email, mobile = false] = member;
      if(typeof first_name != 'string' || typeof last_name != 'string' || typeof email != 'string')
        return res.status(400).send(`Member ${number}: The names and the email properties should be strings.`);
      
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if(!emailRegex.test(email))
        return res.status(400).send(`Member ${number}: The third value should be an email`);
      
      if(mobile && typeof mobile !== 'undefined' && isNaN(Number(mobile)))
        return res.status(400).send(`Member ${number}: The fourth value should be a number or empty.`);
      
      if(await this.mongo.db.collection('users').findOne({email}))
        continue;
      
      if(mobile && await this.mongo.db.collection('users').findOne({mobile}))
        continue;
      
      newMembers.push({
        first_name, last_name, email, mobile,
        mode: 'created',
        type: 'member',
        createdBy: new this.mongo.ObjectId(req.user.admin_id || req.user.uuid),
        created_at: Date.now(),
        status: 'pending',
        password: await bcrypt.hash(Math.random().toString(36).slice(-8), 10),
        
      });
      
      
    }
    
    if(newMembers.length == 0)
      return {message: '0 members were added. '};
    const result = await this.mongo.db.collection('users').insertMany(newMembers);
    return {message: `${result.insertedCount} members were added.` };
  } catch(error) {
    return res.send(error);
  }
}

exports.approvedMembers = async function(req, res) {
  try {
    const user_id = new this.mongo.ObjectId(req.user.admin_id || req.user.uuid);
    const members = await this.mongo.db.collection('users').aggregate([
    { $lookup: {
      from: 'userMembers',
      localField: '_id',
      foreignField: 'member_id',
      as: 'member'
      
    }},
    
    { $match: {
      'member.user_id': user_id,
      'member.status': 'approved'
    }},
    
    { $project: {
      id: '$_id', name: {$concat: ['$first_name', ' ', '$last_name']}
    }}
    ]).toArray();
    return members;
  } catch(error) {
    return res.send(error);
  }
}
