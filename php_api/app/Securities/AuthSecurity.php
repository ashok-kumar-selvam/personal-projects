<?php

namespace App\Securities;
use \App\Securities\BaseSecurity;

use Firebase\JWT\JWT;

class AuthSecurity extends BaseSecurity 
{
protected $longterm_key = 'cskhfuwt48wbfjn3i4utnjf38754hf3yfbjc93758thrjsnf83hcwn8437';
protected $shortterm_key = 'tskhfuwt48wbfjn3i4utnjf38754hf3yfbjc93758thrjsnf83hcwn8437';


public function longterm_encript(array $data) {
$payload = [
'iat' => time(),
'nbf' => time()];

$payload = array_merge($payload, $data);
return JWT::encode($payload, $this->longterm_key);

}

public function longterm_decript() {
$header = $this->request->getServer('HTTP_AUTHORIZATION');
if(!$header)  {
$this->setError(' Unable to find the token. ');
return false;
}

$token = explode(' ', $header);
if(!isset($token[1])) {
$this->setError(' Unable to find the token. ');
return false;

}
$token = $token[1];


try {
$cript = JWT::decode($token, $this->longterm_key, ['HS256']);
return (array) $cript;
} catch (\Throwable $th) {
$this->setError(' Invalid attempt ');
return false;

}
$this->setError('unknown error');
return false;

}

public function shortterm_encript(array $data, $remember = false) {
$payload = [
'iat' => time(),
'nbf' => time(),
'exp' => time()+(3600*6)];
if($remember == true)
$payload['exp'] = time()+(3600*1000);


$payload = array_merge($payload, $data);
return JWT::encode($payload, $this->shortterm_key);
}

public function shortterm_decript() {
$header = $this->request->getServer('HTTP_AUTHORIZATION');
if(!$header)  {
$this->setError(' Unable to find the token. ');
return false;
}

$token = explode(' ', $header)[1];
try {
$cript = JWT::decode($token, $this->shortterm_key, ['HS256']);
return (array) $cript;
} catch (\Throwable $th) {
$this->setError(' Invalid attempt ');
return false;

}
$this->setError('unknown error');
return false;
}




}
