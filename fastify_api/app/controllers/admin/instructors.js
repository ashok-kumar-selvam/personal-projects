const getCredentials = require('../../helpers/getCredentials');
const bcrypt = require('bcrypt');
exports.create = async function(req, res) {
  try {
    const {body} = req;
    const name = body.name.replace(' ', '').trim();
    if(name.length < 4)
      return res.status(400).send(`The name should be 4 in length without any spaces.`);
    let {userId, password} = await getCredentials(name, this.mongo.db.collection('users'));
    
    if(await this.mongo.db.collection('users').findOne({email: body.email}))
      return res.status(400).send(`This email id is already registered with an other account.`);
    
    let data = {...body, userId,  
    password: await bcrypt.hash(password, 10),
    admin_id: new this.mongo.ObjectId(req.user.uuid),
    type:  'instructor',
    status: 'active',
    created_at: Date.now()
    };
    
    const result = await this.mongo.db.collection('users').insertOne(data);
    
    
    return {user_id: userId, password};
  } catch(error) {
    return res.send(error);
  }
}

exports.update = async function(req, res) {
  try {
    const {body} = req;
    const _id = new this.mongo.ObjectId(req.params.instructor_id);
    const instructor = this.mongo.db.collection('users').findOne({_id, type: 'instructor'});
    
    const result = await this.mongo.db.collection('users').updateOne({_id}, {$set: {...instructor, ...body}});
    return {id: _id};
  } catch(error) {
    if(error.code === 11000) {
      const field = Object.keys(error.keyValue)[0];
      const value = error.keyValue[field];
      return res.status(400).send(`The ${field} field with value ${value} 
      is already registered. Please try again with different value`);
    }
    if(error.code == 500) {
      console.log(error);
      return res.status(500).send('An server error occured. Please try again or contact the admin');
    }
    
    return res.send(error);
  }
}

exports.show = async function(req, res) {
  try {
    const _id = new this.mongo.ObjectId(req.params.instructor_id);
    const instructor = await this.mongo.db.collection('users').findOne({_id, type: 'instructor'});
    delete instructor['password'];
    return {instructor: {...instructor, id: instructor._id}};
  } catch(error) {
    return res.send(error);
  }
}

exports.list = async function(req, res) {
  try {
    const instructors =  await this.mongo.db.collection('users').aggregate([
    { $match: {admin_id: new this.mongo.ObjectId(req.user.uuid), type: 'instructor'}},
      { $project: {
        _id: 0, id: '$_id', name: 1, created_at: 1, email: 1, userId: 1
      }},
      
    ]).toArray();
    return instructors;
  } catch(error) {
    return res.send(error);
  }
}

exports.delete = async function(req, res) {
  try {
    const result = await this.mongo.db.collection('users').deleteOne({_id: new this.mongo.ObjectId(req.params.instructor_id), type: 'instructor'});
    
    if(result.deletedCount != 1)
      return res.status(400).send(`Unable to delete.`);
    
    return 'success';
  } catch(error) {
    return res.send(error);
  }
}