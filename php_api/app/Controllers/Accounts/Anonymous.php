<?php

namespace App\Controllers\Accounts;

use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;

class Anonymous extends ResourceController
{
protected $modelName = '\App\Models\AnonymousUsers';
protected $format = 'json';

public function __construct() {
helper('Auth');
$this->keys = new \App\Models\Keys;
$this->email = new \App\Controllers\Emails;
$this->au = new \App\Securities\AuthSecurity;
}


public function checkEmail() {
$email = $this->request->getVar('email');
if(!$email)
return $this->fail('The email is not found. ');
$user = $this->model->where('email', $email)->first();
if(!$user)
return $this->fail('The email is not registered in anonymous users category. ');

$key = $this->keys->getKey();
$payload = [
'key' => $key,
'to' => $email,
'expire' => time()+7200];
$this->keys->insert($payload);

$result = $this->email->sendinblue([
'name' => $user->name,
'email' => $user->email,
'subject' => 'OTP to verify your email',
'message' => "Some one is trying to view your result. Please use the otp to login. If it is not you, Please avoid this email",
'file' => 'forgot.php',
'key' => $key,
], true, 2);
if($result === true)
return $this->respond('success');
return $this->fail(['message' => 'We are not able to send the email. Please try again later. The error is '.$result]);


}

public function checkOtp() {
$rules = [
'email' => 'required|is_not_unique[anonymous_users.email]',
'otp' => 'required|is_not_unique[keys.key]'];

$errors = [
'email' => ['is_not_unique' => 'The email is not found in the anonymous users list. '],
'otp' => ['is_not_unique' => 'The OTP is invalid.'],
];

if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();
$result = $this->keys->where('to', $request['email'])->where('key', $request['otp'])->where('expire >', time())->first();
if(!$result)
return $this->fail(' Invalid OTP found Please regenerate the otp.');
$this->keys->delete($result->id);
$user = $this->model->where('email', $request['email'])->first();
$token = $this->au->shortterm_encript(['uuid' => $user->id, 'email' => $user->email], true);

return $this->respond([
'temptoken' => $token]);

}

}
