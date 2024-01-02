const register = require('./Register.js');
const verify = require('./Verify');
const login = require('./Login');
const {otp, recentOtp, forgot} = require('./Otp');
const resend = require('./Resend');
const newPassword = require('./NewPassword');
const anonymous = require('./AnonymousRegistration.js');

module.exports = {
    register, login, verify, forgot, otp, newPassword, resend, recentOtp, anonymous
}