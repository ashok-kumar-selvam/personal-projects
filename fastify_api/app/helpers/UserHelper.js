const getUser = async (req) => {
  try {
    await req.jwtVerify();
    return req.user;
  } catch(error) {
    console.error(error);
    return false;
  }
}

const hasPermission = async function(rq, rs, fy) {
  try {
    const entity   = rq.routerPath.split('/')?.[2];
    const {method} = rq;
    const id = new fy.mongo.ObjectId(rq.user.uuid);
    
    const action = method == 'GET' ? 'view':
    method == 'POST' ? 'create':
    method == 'PUT' || method == 'PATCH' ? 'edit':
    method == 'DELETE' ? 'delete':method;
    const cacheKey = `${entity}_${id}_${action}`;
    if(await fy.cache.has(cacheKey))
      return true;
    
    const cached = await fy.cache.get(`permissions_${id}`);
    
    let permissions;
    if(!cached) {
      const instructor = await fy.mongo.db.collection('users').findOne({_id: id, type: 'instructor'});
      if(!instructor)
        return rs.status(400).send(`Unable to find the instructor account.`);
    permissions = instructor.permissions;
    console.log('The instructor retreived');
    } else {
      permissions = cached.item;
      console.log('the permission taken from cache');
    }
    
    
    
    
    if(!permissions)
      return rs.status(400).send(`Unable to find the permissions.`);
    
    const permission = permissions[entity];
    if(!permission)
      return rs.status(400).send(`The ${entity} permission was not given to you.`);
    
    if(!permission.includes(action))
      return rs.status(400).send(`You are not allowed to ${action} the ${entity}`);
    
    if(!cached)
      await fy.cache.set(`permissions_${id}`, permissions, (60000*10));
    
    await fy.cache.set(cacheKey, 'yes', (60000*10));
    return true;
  } catch(error) {
    
    return rs.send(error);
  }
}


const isOwner  = async function(req, mongo, cache  = false) {
  try {
    const excludes = ['result_id', 'attempt_id'];
    
    const id  = req.routerPath.match(/:(\w+)/)?.[1];
    
    if(!id || excludes.includes(id))
      return true;
    const value = req.params[id];
    const user_id = req.user.admin_id ||  req.user.uuid;
    const cacheKey  = `${id}_${value}_${user_id}`;
    if(await cache.has(cacheKey)) 
      return true;
    
    const _id = new mongo.ObjectId(req.params[id]);
    const db = mongo.db;
    let entity;
    
    switch(id) {
      case "exam_id":
        entity = await db.collection('exams').findOne({_id: new mongo.ObjectId(value)});
      break;
      case "assign_id":
        entity  = await db.collection('assignments').findOne({
          _id: new mongo.ObjectId(value)});
      break;
      case "group_id":
        entity = await db.collection('groups').findOne({
          _id: new mongo.ObjectId(value)
        });
      break;
      case "member_id":
        entity = await db.collection('userMembers').findOne({
          member_id: _id
        });
      break;
      case "question_id":
        entity = await db.collection('questions').findOne({
          _id: new mongo.ObjectId(value)
        });
      break;
      case "quiz_id":
        entity = await db.collection('quizzes').findOne({_id: new mongo.ObjectId(value)});
      break;
      case "entity_id":
        entity = await db.collection('exams').findOne({_id})
        || await db.collection('quizzes').findOne({_id});
      break;
      case 'instructor_id':
        entity = await db.collection('users').findOne({_id, type: 'instructor' });
      break;
      
      default:
        throw new Error(`The ${id} is not properly handled in ownership checking. `);
    }
    
    if(!entity || (entity.user_id != user_id && entity.admin_id != user_id)) 
      return false;

    await cache.set(cacheKey, 'yes', (60000 *720));
    
    return true;
  } catch(error) {
    console.error(error);
    return false;
  }
}


const authoriseAdmin = async function(req, res) {
  try {
    await req.jwtVerify();
    

    if(req.user.type != 'admin' && req.user.type != 'instructor' )
      return res.status(401).send(`You are not allowed to access this endpoint.`);
    
    if(req.user.status != 'approved' && req.user.status != 'active')
      return res.status(400).send(`Your account is deactivated.`);

    if(!await isOwner(req, this.mongo, this.cache))
      return res.status(403).send(`Unable to verify the ownership of entity.`);
    
    if(req.user?.type == 'instructor')
      await hasPermission(req, res, this);
  } catch(error) {
    res.send(error);
  }
}

module.exports = {getUser, hasPermission, isOwner, authoriseAdmin};