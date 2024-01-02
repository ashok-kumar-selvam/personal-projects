const th = require('../../../helpers/tokenHelper');

module.exports = async function(t) {
  try {
    const headers = th.getHeader();
    
    const method = 'POST';
    const url = '/admin/members';
    const payload = {};
    const app = t.context.app;


    // should generate 401 error
    const notAllowed = await app.inject({method, url, payload});
    t.equal(notAllowed.statusCode, 401, 'The header should be set');

    const missingParams4 = await app.inject({method, url, payload, headers});
    t.equal(missingParams4.statusCode, 400, 'The required 4 params are missing');
    
    payload.first_name = 'ashokkumar';
    const missingEmail = await app.inject({method, url, payload, headers});
    t.equal(missingEmail.statusCode, 400, 'Email and mobile is missing ');

    payload.email = process.env.TEST_EMAIL1;
    const missingMobile = await app.inject({method, url, payload, headers});
    t.equal(missingMobile.statusCode, 400, 'The mobile missing error ');

    payload.mobile = '8220085801';
    const uniqueEmailError = await app.inject({method, url, payload, headers});
    t.equal(uniqueEmailError.statusCode, 400, 'The email is aalready registered');
    t.equal(uniqueEmailError.body, t.context.config.getMessage('emailAlreadyRegistered'));

    payload.email = 'demo@example.com';
    const uniqueMobileError = await app.inject({method, url, payload, headers});
    t.equal(uniqueMobileError.statusCode, 400, 'The mobile is not unique');
    t.equal(uniqueMobileError.body, t.context.config.getMessage('mobileAlreadyRegistered'));

    payload.mobile = '1122334455';
    const success = await app.inject({method, url, payload, headers});
    t.equal(success.statusCode, 200, 'Successfully registered the member');
    
  } catch(error) {
    throw error;
  }
}