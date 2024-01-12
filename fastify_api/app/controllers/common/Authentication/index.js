const register = require('./Register.js');
const verify = require('./Verify.js');
const login = require('./Login.js');
const {otp, recentOtp, forgot} = require('./Otp.js');
const resend = require('./Resend.js');
const newPassword = require('./NewPassword.js');
const anonymous = require('./AnonymousRegistration.js');

module.exports = {
    register, login, verify, forgot, otp, newPassword, resend, recentOtp, anonymous
}