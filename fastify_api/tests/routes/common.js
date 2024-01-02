const authentication  = require('../controllers/common/authentication');

module.exports = async function(t) {
  
  await t.test('register', authentication.register);
  await t.test('resendemail', authentication.resend);
  await t.test('verifyemail', authentication.verify);
  await t.test('login', authentication.login);
  await t.test('forgot', authentication.forgot);
  await t.test('otp', authentication.otp);
  await t.test('newpassword', authentication.newPassword);
}