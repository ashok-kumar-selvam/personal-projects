const admins = require('../controllers/member/admins');
const entities = require('../controllers/member/entities');

module.exports = async function(fastify, opts) {
  fastify.addHook('onRequest', async function(req, res) {
    try {
      await req.jwtVerify();
      if(req?.user?.type != 'member')
        return res.status(400).send('You are not allowed to access this endpoint');
      
      if(req?.user?.status == 'pending')
        return res.status(400).send('Your status is pending. Please verify your account to continue.')
    } catch(error) {
      res.send(error)
    }
  });
  
  fastify.get('/admins/verify/:admin_code', admins.verify);
  fastify.post('/admins/request', admins.request);
  fastify.get('/exams', entities.exams);
 fastify.get('/quizzes', entities.quizzes);
 fastify.get('/quizzes/results', entities.quizResults);
 
  
}