


exports.register = async function(t) {

  const app = t.context.app;
  const [method, url, email] = ['POST', '/register', 'asokmadurai1@gmail.com'];  
  const email2 = 'asokmadurai2@gmail.com';
  
  // empty payload expecting 400 error
  let  payload = {};
  const response1 = await app.inject({method, url, payload});
  t.equal(response1.statusCode, 400, 'empty payload expecting 400 error');
  
  // payload with only first_name 
  payload.name = 'ashokkumar';
  const response2 = await app.inject({method, url, payload});
  t.equal(response2.statusCode, 400, 'only first name');
  
  //payload with names only
  
  const response3 = await app.inject({method, url, payload});
  t.equal(response3.statusCode, 400, 'only names');
  
  // payload with email
  payload.email = process.env.TEST_EMAIL1;
  const response4 = await app.inject({method, url, payload});
  t.equal(response4.statusCode, 400, 'names and email');
  
  //payload with single password
  payload.password = process.env.TEST_PWD;
  const response5 = await app.inject({method, url, payload});
  t.equal(response5.statusCode, 400, 'without confirm password');
  
  //payload with passwords
  payload.confirm_password = process.env.TEST_PWD;
  const response6 = await app.inject({method, url, payload});
  t.equal(response6.statusCode, 400, 'without mobile and type');
  
  //Should generate missing mobile error
  payload.type = 'admin';
  const response7 = await app.inject({method, url, payload});
  t.equal(response7.statusCode, 400, 'failure due to missing mobile number');
  
  
  
  //successful admin registration
  payload.mobile = '8220085801';
  const response8 = await app.inject({method, url, payload});
  t.equal(response8.statusCode, 200, 'successful admin registration');
  
  //should generate unique validation error
  payload.type = 'member';
  const response9 = await app.inject({method, url, payload});
  t.equal(response9.statusCode, 409, 'unique validation error for email ');
  
  //successful member registration
  payload.email = process.env.TEST_EMAIL2;
  payload.mobile = '1122332211';
  const response10 = await app.inject({method, url, payload});
  t.equal(response10.statusCode, 200, 'Successful registration of member ');
}

exports.resend = async function(t) {
  const app = t.context.app;
  const getKey = await  app.inject({method: 'GET', url: `/recentotp?email=${process.env.TEST_EMAIL1}`});
  
  t.equal(getKey.statusCode, 200, 'The recent otp ');
  const payload = getKey.json();
  const response1 = await app.inject({
    url: `/resendemail/${process.env.TEST_EMAIL1}`,
    method: 'GET'
  });
  
  t.equal(response1.statusCode, 400, 'The email was sent instead of the user_id');
  
  const response2 = await app.inject({
    method: 'GET',
    url: `/resendemail/${payload.user_id}`,
    
  });
  t.equal(response2.statusCode, 200, 'The resend is successful ');
  
}



exports.verify = async function(t) {
  const app = t.context.app;
  const url = '/verifyemail';
  const method = 'POST';
  const getKey = await app.inject({method: 'GET', url: `/recentotp?email=${process.env.TEST_EMAIL1}`});
  
  t.equal(getKey.statusCode, 200, 'The otp key ');
  const payload = getKey.json();
  const response1 = await app.inject({method, url, payload});
  t.equal(response1.statusCode, 200, 'Verify email');
}

exports.login = async function(t) {
  const app = t.context.app;
const url = '/login';
const method = 'POST';

let payload = {};

  const response1 = await app.inject({method, url, payload});
  t.equal(response1.statusCode, 400, 'Responnse1');
  
  payload.username = 'asokmadurai5@gmail.com';
  payload.password = '1234';
  
  const response2 = await app.inject({method, url, payload});
  t.equal(response2.statusCode, 404, 'Response2');
  
  payload.username = process.env.TEST_EMAIL1;
  const response3 = await app.inject({method, url, payload});
  t.equal(response3.statusCode, 400, 'Response3');
  
  payload.password = process.env.TEST_PWD;
  const response4 = await app.inject({method, url, payload});
  t.equal(response4.statusCode, 200, 'Response4');
t.ok(response4.json().token, 'The login token');
  
  
}

exports.forgot = async function(t) {
  const app = t.context.app;
  const method = 'POST';
  const response1 = await app.inject({method, url: '/forgot', payload: {email: 'asokmadurai5@gmail.com'}});
  t.equal(response1.statusCode, 404, 'The response1');
  
  const response2 = await app.inject({
    method, url: '/forgot',
    payload: {email: process.env.TEST_EMAIL1}
  });
  t.equal(response2.statusCode, 200, 'The response2');
  
}

exports.otp = async function(t) {
  const {app} = t.context;
  const url = '/otp';
  const method = 'POST';
  const email = process.env.TEST_EMAIL1;
  const response1 = await app.inject({method, url, payload: {}});
  t.equal(response1.statusCode, 400, 'response1');
  
  const response2 = await app.inject({method, url, payload: {email}});
  t.equal(response2.statusCode, 400, 'response2');
  
  const response3 = await app.inject({method, url, payload: {email, otp: 223344}});
  t.equal(response3.statusCode, 400, 'response3');
  
  const getKey = await app.inject({method: 'GET', url: `/recentotp?email=${process.env.TEST_EMAIL1}`});
  t.ok(getKey, 'the key');
  const key = getKey.json();
  
  
  
  const response4 = await app.inject({method, url, 
  payload: {email, otp: key.key}});
  t.equal(response4.statusCode, 200, 'the response4');
  
  
}


exports.newPassword = async function(t) {
  const {app} = t.context;
  const url = '/newpassword';
  const method = 'POST';
  const email = process.env.TEST_EMAIL1;

  const getKey = await app.inject({method: 'GET', url: `/recentotp?email=${email}`});
  t.ok(getKey, 'The key did not found');
  const key = getKey.json();
  const response1 = await app.inject({method, url,
  payload: {
    email: 'asokmadurai5@gmail.com',
    otp: key.key,
    new_password: 'aabbccddee',
    confirm_password: 'aabbccddee',
    
  }});
  t.equal(response1.statusCode, 404, 'response1');
  
  const response2 = await app.inject({method, url, payload: {
    email, otp: 00001,
    new_password: 'aabbccddee', confirm_password: 'aabbccddee'
  }});
  t.equal(response2.statusCode, 404, 'the response2');
  
  const response3 = await app.inject({method, url, payload: {
    email, otp: key.key,
    new_password: 'vinayaga', confirm_password: 'vinayaga'
  }});
  t.equal(response3.statusCode, 400, 'the response3');
  
  const response4 = await app.inject({method, url, payload: {
    email, otp: key.key,
    new_password: 'ashokkumar', confirm_password: 'aabbccddee'
  }});
  t.equal(response4.statusCode, 400, 'the response4');
  
  const response5 = await app.inject({method, url, payload: {
    email, otp: key.key,
    new_password: 'ashokkumar', confirm_password: 'ashokkumar'
  }});
  t.equal(response5.statusCode, 200, 'the response5');
  

  // confirm the password change
  const login = await app.inject({method: 'POST', url: '/login', payload: {username: process.env.TEST_EMAIL1, password: 'ashokkumar'}});
  t.equal(login.statusCode, 200, 'The login successful');
  process.env.TEMP_TOKEN = login.json().token;
  
}

