const {getKey} = require('../../../helpers/dbHelper');

module.exports = async function(req, res) {
    try {
      const user_id  = req.params.user_id;
      const users = this.mongo.db.collection('users');
      const keys = this.mongo.db.collection('keys');
      const ObjectId = this.mongo.ObjectId;
      
      const user = await users.findOne({_id: new ObjectId(user_id)});
      
      if(!user)
        return res.status(400).send('The user is not found.');
      
      if(user.status == 'approved')
        return res.status(400).send('The user is already verified.');
      
      
      const key = await getKey(keys, user.email, (24*86400)); 
      const mailOption = {
        to: user.email,
        key: key,
        subject: "Please verify whether it is you",
        template: 'verify',
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