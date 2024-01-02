const xlsx = require('xlsx');
const {XlsxUploadHelper, TxtUploadHelper, UploadHelper} = require('../../helpers');

exports.getcount = async function(req, res) {
  try {
    const questionPaper = this.mongo.db.collection('questionPaper');
    const count = await questionPaper.countDocuments({entity_id: new this.mongo.ObjectId(req.query.entity_id)});
    return {count};
  } catch(error) {
    return res.status(400).send(error.messsage);
  }
}

exports.create = async function(req, res) {
  try {
    const questions = this.mongo.db.collection('questions');
    const questionPaper = this.mongo.db.collection('questionPaper');
    const ObjectId = this.mongo.ObjectId;
    
    
    
    let  {body} = req;
    
    
    body.created_at = Date.now();
    body.source_id =  new ObjectId(body.source_id);
    body.user_id = new ObjectId(req.user.admin_id || req.user.uuid);
    
    const result = await questions.insertOne(body);
    const result2 = await questionPaper.insertOne({
      entity_id: new ObjectId(body.source_id),
      question_id: result.insertedId
    });
    const count = await questionPaper.countDocuments({entity_id: new ObjectId(body.source_id)});
    
    return {count, 'success': true}
  } catch(error) {
    return res.status(400).send(error.message);
  }
}

exports.show = async function(req, res) {
  try {
    const _id = new this.mongo.ObjectId(req.params.question_id);
    const question = await this.mongo.db.collection('questions').findOne({_id}, { projection: {
      _id: 0, id: '$_id',
      question: 1, type: 1, options: 1, answer: 1,
      settings: 1, point: 1, mpoint: 1, explanation: 1,
      
    }});
    if(!question)
      return res.status(404).send('Unable to find the question. ');
    return question;
  } catch(error) {
    return res.status(400).send(error.message);
  }
}

exports.update = async function(req, res) {
  try {
    let data = req.body;
    let _id = new this.mongo.ObjectId(req.params.question_id);
    delete data['type'];
    console.log('the incoming data is ', data);
    const result = await this.mongo.db.collection('questions').updateOne({_id}, {$set: data});
    
    if(result.modifiedCount != 1)
      return res.status(400).send(`Unable to update the question. ${modifiedCount} rows were updated. `);
    
    return 'success';
  } catch(error) {
    console.error(error);
    return res.status(500).send('Internal server error. ');
  }
}

exports.remove = async function(req, res) {
  try {
    const entity_id = new this.mongo.ObjectId(req.body.entity_id);
    const question_id = new this.mongo.ObjectId(req.body.question_id);
    
    const question = await this.mongo.db.collection('questions').findOne({_id: question_id});
    
    if(!question)
      return res.status(404).send('Unable to find the question.');
    
    const  entity = await this.mongo.db.collection('exams').findOne({_id: entity_id})
    || await this.mongo.db.collection('quizzes').findOne({_id: entity_id});
    
    if(!entity)
      return res.status(404).send('The entity is not found.');
    
    if(entity.user_id != req.user.uuid && entity.user_id != req.user.admin_id)
      return res.status(403).send('The ownership can not be approved.');
    
    const result = await this.mongo.db.collection('questionPaper').deleteOne({entity_id, question_id});
    if(result.deletedCount != 1)
      return res.status(400).send('Invalid request');
    return 'success';
  } catch(error) {
    return res.status(500).send(error.message);
  }
}

exports.bank = async function(req, res) {
  try {
    const entity_id = new this.mongo.ObjectId(req.params.entity_id);
    const user_id = new this.mongo.ObjectId(req.user.admin_id || req.user.uuid);
    
    const questions = await this.mongo.db.collection('questions').aggregate([
    { $lookup: {
      from: 'questionPaper',
      localField: '_id',
      foreignField: 'question_id',
      as: 'questionPaper'
    }},
    { $match: {
      user_id: user_id,
      'questionPaper.entity_id': {$ne: entity_id}
    }},
    
    
    { $lookup: {
      from: 'exams',
      localField: 'source_id',
      foreignField: '_id',
      as: 'exams',
      
    }},
    { $lookup: {
      from: 'quizzes',
      localField: 'source_id',
      foreignField: '_id',
      as: 'quizzes'
    }},
    
    
    { $addFields: {
      title: {
        $cond: [
        {$eq: [{$size: '$exams'}, 0]}, 
          {
            $cond: [
            {$eq: [{$size: '$quizzes'}, 0]},
          'deleted source',
          {$arrayElemAt: ['$quizzes.title', 0]}
          ],
          },
      {$arrayElemAt: ['$exams.title', 0]}
      ]}
    }},
    
    { $project: {
      _id: 0, id: '$_id', title: 1, question: 1,type: 1,
      point: 1, mpoint: 1, created_at: 1,
    }}
    ]).sort({created_at: -1}).toArray();
    console.log('the first question is ', questions[0]);
    return questions;
  } catch(error) {
    return res.send(error);
  }
}

exports.add = async function(req, res) {
  try {
    const entity_id = new this.mongo.ObjectId(req.params.entity_id);
    const questions = req.body.map(id => new this.mongo.ObjectId(id));
    const userId = new this.mongo.ObjectId(req.user.admin_id || req.user.uuid);
    
    const result1 = await this.mongo.db.collection('questions').aggregate([
    { $match: {
      _id: {$in: questions},
      user_id: userId
    }},
    {$count: 'count'}
    ]).toArray();
    
    const totalCount = result1.length > 0 ? result1[0].count: 0;
    
    if(totalCount != questions.length)
      return res.status(400).send(`Invalid request`);
    const data = questions.map(question_id => ({entity_id, question_id}));
    const result = await this.mongo.db.collection('questionPaper').insertMany(data);
    
    if(result.insertedCount != data.length)
      return res.status(400).send('Unexpected error. Not all of the questions were added. ');  
    return 'success';
  } catch(error) {
    return res.send(error);
  }
}

exports.upload = async function(req, res) {
  try {
    const data = req.body;
    const entity_id = new this.mongo.ObjectId(data.entity_id);
    const entityType = await this.mongo.db.collection('exams').findOne({_id: entity_id}) ?
      'exam':
      await this.mongo.db.collection('quizzes').findOne({_id: entity_id}) ? 'quiz': 'unknown';
    
    if(data.file_type == 'xlsx') {
      const xlUpload = new XlsxUploadHelper(data.question_count, data.file_type, entityType, data);

      if(!xlUpload.prepare())
        return res.status(400).send(xlUpload.getError());

      return res.send({
        questions: xlUpload.getQuestions(),

      });
    } else if(data.file_type == 'txt') {
      const txtUpload = new TxtUploadHelper(data.question_count, data.file_type, entityType, data);
      if(!txtUpload.prepare())
        return res.status(400).send(txtUpload.getError());
      return res.send({
        questions: txtUpload.getQuestions(),
      });
    }
    return res.status(400).send("Unexpected place error");
    let result, qs;
    
    
    if(!result)
      return res.status(400).send(qs);

    

    const documents = qs.map(question => {
      return {...question, source: entityType, source_id: entity_id,
      user_id: new this.mongo.ObjectId(req.user.admin_id || req.user.uuid)};
    });

    const {insertedIds} = await this.mongo.db.collection('questions').insertMany(documents);

    const documents2 = Object.keys(insertedIds).map(key => ({entity_id, question_id: insertedIds[key]}));

    const {insertedCount} = await this.mongo.db.collection('questionPaper').insertMany(documents2);

    if(insertedCount == documents2.length)
      return 'success';

    return res.status(400).send(`Unexpected error. only ${insertedCount} questions were added.`);
  } catch(error) {
    return res.send(error);
  }
}

exports.bulkCreate = async function(req, res) {
  try {
    const {body} = req;
    const user_id = new this.mongo.ObjectId(req.user.admin_id ?? req.user.uuid);
    const users = this.mongo.db.collection('users');
    const exams = this.mongo.db.collection('exams');
    const quizzes = this.mongo.db.collection('quizzes');
    const questions = this.mongo.db.collection('questions');
    const questionPaper = this.mongo.db.collection('questionPaper');
    const source_id = new this.mongo.ObjectId(body.entity_id);
    const source = await exams.findOne({_id: source_id}) ? 'exam': await quizzes.findOne({_id: source_id}) ? 'quiz': 'unknown';
    const [result, values] = UploadHelper.validate(body.questions);
    console.log('the values is ', values);
    if(!result)
      return res.status(400).send(values);
    
    const preparedQuestions = values.map(q => ({...q, source, source_id, user_id}));
    const {insertedIds} = await questions.insertMany(preparedQuestions);
    const idsList = Object.keys(insertedIds).map(key => ({entity_id: source_id, question_id: insertedIds[key]}));
    const {insertedCount} = await questionPaper.insertMany(idsList);
    return insertedCount == idsList.length ? res.send('success'): res.status(400).send(`We were able to upload ${insertedCount} questions. `);
  } catch(error) {
    console.log(error);
    return res.status(400).send('error occured');
  }
}
exports.clear = async function(req, res) {
  try {
    const entity_id = new this.mongo.ObjectId(req.params.entity_id);
    const result = await this.mongo.db.collection('questionPaper').deleteMany({entity_id});
    
    if(result.deletedCount <= 0)
      return res.status(400).send(`Unable to delete any question.`);
    return 'success';
  } catch(error) {
    return res.send(error);
  }
}
