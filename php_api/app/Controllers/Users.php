<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
//{"user_id":"asho9004","password":"0azAjGWo"}
class Users extends ResourceController
{
protected $modelName = 'App\Models\Users';
protected $format = 'json';

public function __construct() {
helper('Auth');
$this->env = getEnv('ci_environment');
$this->keys = new \App\Models\Keys;
$this->email = new \App\Controllers\Emails;
$this->instructors = new \App\Controllers\Users\Instructors;
$this->credits = new \App\Subscriptions\Credits;
}

public function testing() {
echo json_encode($this->model->findAll());

}
public function login() {

$data = (object) $this->request->getVar();
if($this->instructors->getInstructor($data->email))
return $this->instructorLogin();

$user = $this->model->where('email', $data->email)->first();
if(!$user) 
return $this->failNotFound(' Unable to find the email/user id');

if(!password_verify($data->password, $user->password))
return $this->fail('Wrong password');
$key = getAuthKey();
$payload = [
'iat' => time(),
'nbf' => time(),

'uuid' => $user->id,
'email' => $user->email,
'type' => $user->type,
'status' => $user->status,
];
$token = JWT::encode($payload, $key);
return $this->respond([
'id' => $user->id,
'name' => $user->first_name." ".$user->last_name,
'type' => $user->type,
'isAuthed' => true,
'status' => $user->status,
'token' => $token]);

}


public function forgot() {

$email = $this->request->getVar('email');
$user = $this->model->where('email', $email)->first();
$instructor = $this->instructors->getInstructor($email);

if(!$user && !$instructor)
return $this->fail(['message' => 'Unable to find the email/user id ']);

$key = $this->setKey($email, 7200);
$result = $this->email->sendinblue([
'name' => $user->first_name ?? $instructor->name,
'email' => $user->email ?? $instructor->email,
'subject' => 'Someone has requested to change your password',
'message' => " We have received a request to change your password. Please use the otp to validate this request. If it is not you, avoid this email.",
'file' => 'forgot.php',
'key' => $key,
], true, 2);
if($result === true)
return $this->respond('success');
return $this->fail(['message' => 'We are not able to send the email. Please try again later. The error is '.$result]);
}

public function otp() {

$data = (object) $this->request->getVar();
$key = $this->checkKey($data->email, $data->otp);

if(!$key)
return $this->fail(['message' => 'Invalid key. Please generate new one ']);

return $this->respond('success');
}

public function new_password() {
$rules = [
'otp' => 'required|is_not_unique[keys.key]',
'email' => 'required',
'new_password' => 'required|min_length[8]|max_length[20]',
'confirm_password' => 'required|matches[new_password]'];
if(!$this->validate($rules))
return $this->failValidationErrors($this->validator->getErrors());

$instructors = new \App\Models\Instructors;

$data = (object) $this->request->getVar();
$key = $this->checkKey($data->email, $data->otp);

if(!$key)
return $this->fail(['message' => 'Invalid key. Please generate new one ']);

$user = $this->model->select(' id, concat(first_name, " ", last_name) as name, email, password')->where('email', $data->email)->first();

$instructor = $this->instructors->getInstructor($data->email);
if(!$user && !$instructor)
return $this->fail(['message' => 'Unable to find the email']);

if($instructor)
$user = $instructor;

if(password_verify($data->new_password, $user->password))
return $this->fail([
'message' => "Don't provide the old password"]);

$password = password_hash($data->new_password, PASSWORD_BCRYPT);

if($instructor) {
$instructors->update($user->id, ['password' => $password]); 
} else if($user) {
$this->model->update($user->id, ['password' => $password]);
}

$this->email->send([
'name' => $user->name,
'email' => $user->email,
'subject' => 'Your password was changed',
'message' => 'This email is to inform you that your password has been changed. If you did not do it, Please contact the admin imediately',
]);
$this->keys->where('key', $data->otp)->delete();
return $this->respond('success');
}

    public function register()
    {

$rules = [
'first_name' => 'required|max_length[250]',
'last_name' => 'required|max_length[250]',
'email' => 'required|is_unique[users.email]|valid_email',
'mobile' => 'if_exist|is_unique[users.mobile]|numeric',
'password' => 'required|min_length[8]|max_length[20]',
'confirm_password' => 'required|matches[password]',
'type' => 'required|in_list[member,admin]',

];

$errors = [
'email' => ['is_unique' => 'The email is already registered. Please try with other email.'],
'mobile' => ['is_unique' => 'The mobile number is already linked with an other account. Please provide a different mobile number.'],
];

if(!$this->validate($rules, $errors)) 
return $this->failValidationErrors($this->validator->getErrors());

$request = (array)  $this->request->getVar();
$request['status'] = 'pending';
//$request['status'] = 'approved';
$entity = new \App\Entities\Users($request);

$id = $this->model->insert($entity);
if(isset($request['referral']))
$this->claimReferral($id, $request['referral']);

if($request['type'] == 'admin') {



$this->credits->subscribe($id, 'gold', '1 year');
//if(!$this->credits->subscribe($id, 'gold'))
//return $this->fail(['message' => $this->credits->getError()]);

$setcredits = $this->credits->setCredits([
'user_id' => $id,
'mode' => 'renewal',
]);
if(!$setcredits)
return $this->fail(['message' => $this->credits->getError()]);
}


$key = $this->setKey($id, (3600*24));
$result = $this->email->sendinblue([
'name' => $request['first_name'],
'email' => $request['email'],
'subject' => 'Please Verify Your Email Id',
'message' => "Please use the following id to verify your email id",
'key' => $key,
'file' => 'otp.php'], true, 1);

if($result === true)
return $this->respond(['id' => $id]);
return $this->fail(['message' => 'The account created successfully. but we are unable to send the verification email. Please verify your email later.']);

    }

public function verify_email() {
$rules = [
'user_id' => 'required|is_not_unique[users.id]',
'key' => 'required|is_not_unique[keys.key]'];

$errors = [
'user_id' => ['is_not_unique' => 'Unable to find the user. Please check again.'],
'key' => ['is_not_unique' => 'Invalid key. Please check and try again'],
];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();


$result = $this->checkKey($request['user_id'], $request['key']);
if(!$result)
return $this->fail(['message' => 'Invalid key']);
$this->model->update($request['user_id'], ['status' => 'approved']);
$this->keys->delete($result->id);
$user = $this->model->where('id', $request['user_id'])->first();
$referrer = db_connect()->table('claimed_referrals')->where('referee', $user->id)->get();
$referrer = $referrer->getRow();
if($referrer) 
$this->credits->setCredits([
'user_id' => $referrer->referrer,
'mode' => 'addon',
'plan_id' => 'addon251']);


$plans = new \App\Models\Plans;
$activePlan = $plans->getActivePlan($user->id);

$key = getAuthKey();
$payload = [
'iat' => time(),
'nbf' => time(),
'uuid' => $user->id,
'email' => $user->email,
'type' => $user->type,
'status' => $user->status];
$token = JWT::encode($payload, $key);
return $this->respond([
'id' => $user->id,
'name' => $user->first_name." ".$user->last_name,
'type' => $user->type,
'isAuthed' => true,
'status' => $user->status,
'has_plan' => $activePlan ? 'yes': 'no',
'token' => $token]);
}

public function resend_email($user_id) {
$user = $this->model->where('id', $user_id)->first();
if(!$user)
return $this->fail(['message' => "Unable to find the user with the user id $id "]);

$key = $this->setKey($user_id, (3600*24));
//if(getEnv('ci_environment') == 'development')
//return $this->fail(['message' => "the key is $key"]);

$result = $this->email->sendinblue([
'name' => $user->first_name,
'email' => $user->email,
'subject' => 'Verify Your Email',
'message' => 'Please verify Your Email to continue. Here is the otp to verify your email. ',
'key' => $key,
'file' => 'otp.php',
], true, 2);

if($result === true)
return $this->respond('success');
return $this->fail(['message' => 'Unable to send the email. Please try later']);
}

public function check_pending() {
$id = $this->request->getVar('id');
$user = $this->model->where('id', $id)->first();
if(!$user)
return $this->failNotFound('The user is not found');
return $this->respond([
'status' => $user->status]);

}

protected function setKey($to, $seconds) {
$key = $this->keys->getKey();

$payload = [
'key' => $key,
'to' => $to,
'expire' => time()+$seconds];
$this->keys->insert($payload);
return $key;
}

public function checkKey($to, $key) {

return $this->keys->where('to', $to)->where('key', $key)->where('expire >', time())->first();
}

public function instructorLogin() {
$instructors = new \App\Models\Instructors;

$data = (object) $this->request->getVar();
$instructor = $instructors->where('user_id', $data->email)->first();
if(!password_verify($data->password, $instructor->password))
return $this->fail('Wrong password');

if($instructor->status == 'suspended')
return $this->fail(['message' => 'Your account is suspended']);

$admin = $this->model->where('id', $instructor->admin_id)->first();
$plans = new \App\Models\Plans;
$activePlan = $plans->getActivePlan($admin->id);
if(!$admin || $activePlan == 'basic' || $activePlan == 'silver')
return $this->fail(['message' => 'Invallid instructor account. ']);

$key = getAuthKey();
$payload = [
'iat' => time(),
'nbf' => time(),
'admin_id' => $instructor->admin_id,
'uuid' => $instructor->id,
'email' => $instructor->email,
'status' => $instructor->status];
$token = JWT::encode($payload, $key);
return $this->respond([
'id' => $instructor->id,
'name' => $instructor->name,
'type' => 'admin',
'isAuthed' => true,
'status' => $instructor->status != 'suspended' ? 'approved': 'suspended',
'token' => $token]);

}

public function claimReferral($user_id, $referral_code) {
$db = db_connect();
$offers = new \App\Subscriptions\Offers;
$code = $offers->getPromoCode();

$referrer = $this->model->where('referral_code', $referral_code)->first();



if($referrer) {


$offer = [
'name' => 'Welcome Offer',
'description' => 'Welcome offer for new members. Please use this promo code within 30 days of your registration. ',
'discount' => 10,
'code' => $code,
'limit' => 1,
'target' => $user_id.'@users',
'product' => 'all',
'expiry' => time()+(86400*30),

];

$offers->new($offer);

$db->table('claimed_referrals')->insert([
'referrer' => $referrer->id,
'referee' => $user_id,
'offer' => $code,
]);

return true;
} else {
$referrer = $this->newReferral($user_id, $referral_code);
if(!$referrer)
return false;
$referrer = (object) $referrer;
if($referrer->discount == 0)
return false;

$offer = [
'name' => $referrer->name,
'description' => $referrer->description,
'discount' => $referrer->discount,
'code' => $code,
'limit' => 1,
'target' => $user_id.'@users',
'product' => 'all',
'expiry' => time()+(86400*365),

];

$offers->new($offer);


return true;
}


}

public function newReferral($user_id, $referral_code) {
$data = ['user_id' => $user_id, 'referral_code' => $referral_code];

$client = \Config\Services::curlrequest();
$response = $client->post('http://localhost:8080/joined-referrals', [
'http_errors' => false, 
'json' => $data]);
$body = json_decode($response->getBody(), true);
if($response->getStatusCode() >= 400) {

return false;
}

return $body;

}


}
