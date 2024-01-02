const {authoriseAdmin} = require('../helpers');
const examCtrl   = require('../controllers/admin/exams');
const validate = require('../validations/admin');
const questions = require('../controllers/admin/questions');
const quizzes = require('../controllers/admin/Quizzes');
const groups = require('../controllers/admin/groups');
const assignments = require('../controllers/admin/assignments');
const instructors = require('../controllers/admin/instructors');
const members = require('../controllers/admin/members');
const Results = require('../controllers/admin/Results');

module.exports = async function(fastify, opts) {
  fastify.addHook('onRequest', authoriseAdmin);
 
 fastify.get('/exams', examCtrl.list);
 fastify.post('/exams', {schema: validate.exams.create}, examCtrl.create);
 fastify.get('/exams/:exam_id', examCtrl.show);
 fastify.put('/exams/:exam_id', {schema: validate.exams.update}, examCtrl.update);
 fastify.delete('/exams/:exam_id', examCtrl.delete);
 
fastify.get('/questions/getcount', {schema: validate.questions.getcount}, questions.getcount)
 fastify.post('/questions', {schema: validate.questions.create}, questions.create);
 fastify.post('/questions/bulk', {schema: validate.questions.bulkCreate}, questions.bulkCreate);
 fastify.get('/questions/:question_id', questions.show)
 fastify.put('/questions/:question_id', {schema: validate.questions.update}, questions.update);
 fastify.post('/questions/remove', {schema: validate.questions.remove}, questions.remove);
 fastify.get('/questions/bank/:entity_id', questions.bank);
 fastify.post('/questions/add/:entity_id', {schema: validate.questions.add}, questions.add)
 fastify.post('/questions/upload',  {schema: validate.questions.upload}, questions.upload);
 fastify.delete('/questions/clear/:entity_id', questions.clear);
 
 fastify.get('/quizzes', quizzes.list);
 fastify.post('/quizzes',  quizzes.create);
 fastify.get('/quizzes/:quiz_id', quizzes.view);
 fastify.put('/quizzes/:quiz_id', quizzes.update);
 fastify.delete('/quizzes/:quiz_id', quizzes.remove);
 fastify.get('/quizzes/:quiz_id/results', quizzes.results);
 fastify.get('/quizzes/results/:result_id', quizzes.result);

 fastify.post('/groups', {schema: validate.groups.create}, groups.create);
 fastify.get('/groups/:group_id', groups.show);
 fastify.put('/groups/:group_id', {schema: validate.groups.update}, groups.update);
 fastify.get('/groups', groups.list);
 fastify.delete('/groups/:group_id', groups.delete);
 fastify.post('/groups/:group_id/members', {schema: validate.groups.addMembers},
 groups.addMembers);
 fastify.delete('/groups/:group_id/members', {schema: validate.removeMember}, 
 groups.removeMember);
  
fastify.get('/groups/:group_id/nonmembers', groups.nonmembers);
 
 fastify.post('/assignments', {schema: validate.assignments.create}, assignments.create);
 fastify.get('/assignments/:assign_id', assignments.show);
 fastify.get('/assignments/:assign_id/results', Results.list);
 fastify.get('/assignments/:assign_id/:segment', {schema: validate.assignments.edit}, assignments.edit);
 fastify.put('/assignments/:assign_id/:segment', {schema: validate.assignments.update}, assignments.update);
 fastify.get('/exams/:exam_id/assignments', assignments.list);
 fastify.patch('/assignments/:assign_id/activate', {schema: validate.assignments.activate}, assignments.activate);
 fastify.patch('/assignments/:assign_id/publish', {schema: validate.assignments.publish}, assignments.publish);
 
 fastify.post('/instructors', {schema: validate.instructors.create}, instructors.create);
 fastify.put('/instructors/:instructor_id', {schema: validate.instructors.update}, instructors.update);
 fastify.get('/instructors/:instructor_id', instructors.show);
 fastify.get('/instructors', instructors.list);
 fastify.delete('/instructors/:instructor_id', instructors.delete);
 
 fastify.get('/members/code', {schema: validate.members.code}, members.code)
 fastify.post('/members', {schema: validate.members.create}, members.create);
 fastify.put('/members/:member_id', {schema: validate.members.update}, members.update);
 fastify.get('/members', members.list);
 fastify.post('/members/upload',  members.upload);
 fastify.get('/members/approvedMembers', members.approvedMembers);
 
}