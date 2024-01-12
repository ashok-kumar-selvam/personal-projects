const authentication = require('../controllers/common/authentication');
const validation   = require('../validations/authentication');
const quizzes = require('../controllers/common/quizzes');
const Test = require('./Test');
const result = require('../controllers/common/Result');
module.exports =  async function(fastify, opt) {

  fastify.post('/register', authentication.register);
  fastify.post('/users/anonymous', authentication.anonymous)
  fastify.post('/verifyemail', authentication.verify);
  fastify.get('/resendemail/:user_id', authentication.resend);
  fastify.post('/login', authentication.login)
  fastify.post('/forgot', authentication.forgot);
  fastify.post('/otp', authentication.otp);
  fastify.post('/newpassword', authentication.newPassword);
  
  if(process.env.NODE_ENV == 'testing')
    fastify.get('/recentotp', authentication.recentOtp);

  fastify.get('/quizzes/:quiz_id', quizzes.attend);
  fastify.post('/quizzes/save', quizzes.save); 
  fastify.get('/quizzes/results/:result_id', quizzes.result);
 
  fastify.post('/test', Test);
  fastify.get('/results/:result_id', result);
}