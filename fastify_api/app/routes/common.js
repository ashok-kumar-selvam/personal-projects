const controller    = require('../controllers/common/Authentication');
const validation   = require('../validations/authentication');
const quizzes = require('../controllers/common/Quizzes');
const Test = require('./Test');

module.exports =  async function(fastify, opt) {
  fastify.post('/register', controller.register);
  fastify.post('/users/anonymous', {schema: validation.anonymous}, controller.anonymous)
  fastify.post('/verifyemail', controller.verify);
  fastify.get('/resendemail/:user_id', controller.resend);
  fastify.post('/login', controller.login)
  fastify.post('/forgot', controller.forgot);
  fastify.post('/otp', controller.otp);
  fastify.post('/newpassword', controller.newPassword);
  
  if(process.env.NODE_ENV == 'testing')
    fastify.get('/recentotp', controller.recentOtp);

  fastify.get('/quizzes/:quiz_id', quizzes.attend);
 fastify.post('/quizzes/save', quizzes.save); 
 fastify.get('/quizzes/results/:result_id', quizzes.result);
 
  fastify.post('/test', Test);
  
}