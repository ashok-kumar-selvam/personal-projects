const { default: S } = require("fluent-json-schema");
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


module.exports = {

  schema: {
    response: {
      200: S.object().prop('message', S.string())
    }
  },

  async handler(req, res) {
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
  }