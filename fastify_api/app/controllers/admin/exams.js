
exports.list = async function(req, res) {
  try {
    const {ObjectId} = this.mongo;
    const exams =   await this.mongo.db.collection('exams').find({
      user_id: new ObjectId(req.user.admin_id || req.user.uuid)
    }, {
      projection: {
        _id: 0, id: '$_id',
        title: 1, category: 1, description: 1, created_at: 1
      }
    }).sort({created_at: -1}).toArray();
    return {exams};
  } catch(error) {
    return res.status(400).send(error.message);
  }
}

exports.show = async function(req, res) {
  try {
    const exams = this.mongo.db.collection('exams');
    const {ObjectId} = this.mongo;
    
    const {exam_id} = req.params;
    const exam = await this.mongo.db.collection('exams').findOne({
      _id: new ObjectId(exam_id)
    }, { projection: {
      _id: 0, id: '$_id',
      title: 1,
      category: 1,
      description: 1,
      created_at: 1,
    }});
    
    const questions = await this.mongo.db.collection('questions').aggregate([
    {$lookup: {
      from: 'questionPaper',
      localField: '_id',
      foreignField: 'question_id',
      as: 'questionPaper'
    }},
    
    {$match: {
      'questionPaper.entity_id': new ObjectId(exam_id)
    }},
    { $project: {
      _id: 0, id: '$_id',
      question: 1, type: 1, options: 1, answer: 1,
      point: 1, mpoint: 1, explanation: 1,
    }}
    
    ]).toArray();
    
    const assignments = await this.mongo.db.collection('assignments').aggregate([
    { $lookup: {
      from: 'users',
      localField: 'assignee.assignee_id',
      foreignField: '_id',
      as: 'users'
    }},
    
    { $lookup: {
      from: 'groups',
      localField: 'assignee.assignee_id',
      foreignField: '_id',
      as: 'groups'
    }},
    
    {$match: {
      exam_id: new ObjectId(exam_id)
    }},
    
    { $project: {
      assign_to: {
        $cond: {
          if: {$eq: ['$assignee.assign_to', 'singleMember']},
          then: {$arrayElemAt: ['$users.first_name', 0]},
          else: {
            $cond: {
            if: {$eq: ['$assignee.assign_to', 'singleGroup']},
            then: {$arrayElemAt: ['$groups.name', 0]},
            else: '$assignee.assign_to'}
          }
        }
      },
      _id: 0, id: '$_id', start: '$time.start', end: '$time.end', created_at: 1, status: 1, name: '$introduction.name',
    }}
    ]).sort({start: -1}).toArray();
    
    const details = {
      created_at: exam.created_at,
      questions: questions.length,
      points: questions.reduce((point, current)  => point+current.point, 0),
      category: exam.category,
      assignments: assignments.length,
      results: await this.mongo.db.collection('results').countDocuments({exam_id: new ObjectId(exam_id)}),
    };
    
    return {exam, questions, assignments, details};
  } catch(error) {
    return res.status(400).send(error.message);
  }
}

exports.create = async function(req, res) {
  try {
    const {title, description, category = ''} = req.body;
    const exams = this.mongo.db.collection('exams');
    const {ObjectId} = this.mongo;
    const data = {title, description, category,
    created_at: Date.now(),
    user_id: new ObjectId(req.user.admin_id || req.user.uuid ),
    };
    
    const result = await exams.insertOne(data);
    return {id: result.insertedId}
  } catch(error) {
    return res.status(400).send(error.message);
  }
}

exports.update = async function(req, res) {
  try {
    let data = req.body;
    const _id = new this.mongo.ObjectId(req.params.exam_id);
    delete data['id'];
    const result = await this.mongo.db.collection('exams').updateOne({_id}, {$set: data});
    
    if(result?.modifiedCount != 1)
      return res.status(400).send(`Error occured while updating. ${result.modifiedCount} updates occured.`);
    return {id: req.params.exam_id};
  } catch(error) {
    return res.status(400).send(error.message);
  }
}

exports.delete = async function(req, res) {
  try {
    const _id = new this.mongo.ObjectId(req.params.exam_id);
    const result1 = await this.mongo.db.collection('exams').deleteOne({_id});
    const result2 = await  this.mongo.db.collection('questionPaper').deleteMany({entity_id: _id});
    return 'success';
    
  } catch(error) {
    return res.status(400).send(error.message);
  }
}