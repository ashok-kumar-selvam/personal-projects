const {authoriseAdmin} = require('../helpers');
const exams = require('../controllers/admin/exams');
const validate = require('../validations/admin');
const questions = require('../controllers/admin/questions');
const quizzes = require('../controllers/admin/quizzes');
const groups = require('../controllers/admin/groups');
const assignments = require('../controllers/admin/assignments');
const instructors = require('../controllers/admin/instructors');
const members = require('../controllers/admin/members');
const results = require('../controllers/admin/results');

module.exports = async function(fastify, opts) {
  fastify.addHook('onRequest', authoriseAdmin);

  fastify.get('/exams', exams.list);
  fastify.post('/exams', exams.create);
  fastify.get('/exams/:exam_id', exams.view);
  fastify.put('/exams/:exam_id', exams.update);
  fastify.delete('/exams/:exam_id', exams.delete);

  // questions endpoint
  fastify.get('/questions/count', questions.count)
  fastify.post('/questions', questions.create);
  fastify.post('/questions/bulk', questions.bulkCreate);
  fastify.get('/questions/:question_id', questions.view)
  fastify.put('/questions/:question_id', questions.update);
  fastify.post('/questions/remove',  questions.remove);
  fastify.get('/questions/bank/:entity_id', questions.bank);
  fastify.post('/questions/add/:entity_id', questions.add)
  fastify.post('/questions/upload',  questions.upload);
  fastify.delete('/questions/clear/:entity_id', questions.clear);

  // quizzes endpoints
  fastify.get('/quizzes', quizzes.list);
  fastify.post('/quizzes',  quizzes.create);
  fastify.get('/quizzes/:quiz_id', quizzes.view);
  fastify.put('/quizzes/:quiz_id', quizzes.update);
  fastify.delete('/quizzes/:quiz_id', quizzes.remove);
  fastify.get('/quizzes/:quiz_id/results', quizzes.results);
  fastify.get('/quizzes/results/:result_id', quizzes.result);

  //groups endpoints
  fastify.post('/groups', groups.create);
  fastify.get('/groups/:group_id', groups.view);
  fastify.put('/groups/:group_id', groups.update);
  fastify.get('/groups', groups.list);
  fastify.delete('/groups/:group_id', groups.delete);
  fastify.post('/groups/:group_id/members', groups.addMembers);
  fastify.delete('/groups/:group_id/members', groups.removeMember);
  fastify.get('/groups/:group_id/nonmembers', groups.nonmembers);

  // assignments endpoints
  fastify.post('/assignments', assignments.create);
  fastify.get('/assignments/:assign_id', assignments.view);
  fastify.get('/assignments/:assign_id/results', results.list);
  fastify.get('/assignments/:assign_id/:segment', assignments.edit);
  fastify.put('/assignments/:assign_id/:segment', assignments.update);
  fastify.patch('/assignments/:assign_id/activate', assignments.activate);
  fastify.patch('/assignments/:assign_id/publish', assignments.publish);

  // instructors endpoints
  fastify.post('/instructors', instructors.create);
  fastify.put('/instructors/:instructor_id', instructors.update);
  fastify.get('/instructors/:instructor_id', instructors.view);
  fastify.get('/instructors', instructors.list);
  fastify.delete('/instructors/:instructor_id', instructors.delete);
 
  // members endpoints
  fastify.get('/members/code', members.code)
  fastify.post('/members', members.create);
  fastify.put('/members/:member_id', members.update);
  fastify.get('/members', members.list);
  fastify.post('/members/upload',  members.upload);
  fastify.get('/members/approvedMembers', members.approvedMembers);

 fastify.get('/results/:attempt_id', results.view);
}