
module.exports= async function(req, res) {
  try {
    const member_id = new this.mongo.ObjectId(req.user.uuid);
    const results = await this.mongo.db.collection('results').aggregate([
    { $lookup: {
      from: 'quizzes',
      localField: 'quiz_id',
      foreignField: '_id',
      as: 'quiz'
    }},
    { $match: {
      member_id: member_id
    }},
    { $project: {
      _id: 0, id: '$_id', title: '$quiz.title',
      created_at: 1, attempt: 1, 
    }}]).sort({created_at: -1, attempt: -1}).toArray();
    return results;
  } catch(error) {
    return res.send(error);
  }
}