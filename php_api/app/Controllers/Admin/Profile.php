<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;

class Profile extends ResourceController
{

public function __construct() {
$this->users = new \App\Models\Users;
$this->is = new \App\Securities\InstructorSecurity;
$this->credits = new \App\Subscriptions\Credits;
$this->plans = new \App\Models\Plans;
$this->ce = new \App\Models\CreditsEarned;
$this->offers = new \App\Subscriptions\Offers;
}

public function getAccount() {
if($this->is->is_instructor())
return $this->fail(['message' => 'You are not allowed to access this resource.']);

$user = $this->users->select('first_name, last_name, email, mobile, referral_code')->where('id', $this->request->user->uuid)->first();
if(!$user)
return $this->failNotFound(' Unable to get your details. ');

$credits = new \App\Credits\Credits;


$user->plan = $this->credits->getActive($this->request->user->uuid);
$user->credits = $this->credits->getAvailable($this->request->user->uuid);

if(empty($user->referral_code) || is_null($user->referral_code)) {
$this->users->setReferralCode($this->request->user->uuid);
return $this->getAccount();
}


return $this->respond($user);
}

public function setAccount() {
if($this->is->is_instructor())
return $this->fail(['message' => 'You are not allowed to access this resource.']);
$rules = [
'id' => 'required|is_not_unique[users.id]',
'email' => 'if_exist|is_unique[users.email,id,{id}]',
'mobile' => 'if_exist|is_unique[users.mobile,id,{id}]',
'first_name' => 'if_exist|min_length[2]|string',
'last_name' => 'if_exist|string',
];

$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$user = $this->users->where('id', $this->request->user->uuid)->first();
if(!$user)
return $this->failNotFound(' Unable to find the account');
$request = (array) $this->request->getVar();
$data = [];
if(isset($request['first_name']))
$data['first_name'] = $request['first_name'];

if(isset($request['last_name']))
$data['last_name'] = $request['last_name'];

if(isset($request['email']))
$data['email'] = $request['email'];

if(isset($request['mobile']))
$data['mobile'] = $request['mobile'];
$this->users->update($this->request->user->uuid, $data);
return $this->respondCreated('successfully created');
}

public function setPassword() {
if($this->is->is_instructor())
return $this->fail(['message' => 'You are not allowed to access this resource.']);

$rules = [
'old_password' => 'required',
'new_password' => 'required|differs[old_password]',
'confirm_password' => 'required|matches[new_password]'];

$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();
$user = $this->users->where('id', $this->request->user->uuid)->first();
if(!$user)
return $this->failNotFound(' unable to find the account ');

if(password_verify($request['new_password'], $user->password))
return $this->fail(['message' => 'Please do not provide the old password. ']);

$password = password_hash($request['new_password'],  PASSWORD_BCRYPT);
$this->users->update($this->request->user->uuid, ['password' => $password]);
return $this->respondCreated('success');

}

public function getPlans() {
if($this->is->is_instructor())
return $this->fail(['message' => 'You are not allowed to access this resource.']);

$plans = $this->plans->where('user_id', $this->request->user->uuid)->where('expires_on >=', time())->orderBy('created_at', 'desc')->findAll();
foreach($plans as $plan) {
$detail = $this->credits->getPremium($plan->plan);
$plan->name = $detail['name'];

}

$addons = $this->ce->where('user_id', $this->request->user->uuid)->where('category', 'addon')->where('to >', time())->findAll();

return $this->respond([
'plans' => $plans,
'addons' => $addons]);
}

public function setPlan($id) {
if(!$this->credits->activate($id))
return $this->fail(['message' => $this->credits->getError()]);
return $this->respond('success');
}

public function getOffers() {
$offers = $this->offers->getOffers($this->request->user->uuid);
return $this->respond([
'offers' => $offers
]);

}

public function getPreferences() {
$preferences = new \App\Models\EmailPreferences;
$preference = $preferences->where('user_id', $this->request->user->uuid)->first();
if(!$preference) {
$preferences->insert(['user_id' => $this->request->user->uuid]);
$preference = $preferences->where('user_id', $this->request->user->uuid)->first();
}
return $this->respond([
'preferences' => $preference
]);

}

public function setPreferences() {
$preferences = new \App\Models\EmailPreferences;
$rules = [
'id' => 'required|is_not_unique[email_preferences.id]',
'members' => 'if_exist|in_list[yes,no]',
'offers' => 'if_exist|in_list[yes,no]',
'credits' => 'if_exist|in_list[yes,no]',

];

$errors = [];

if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$request = (array) $this->request->getVar();
if(isset($request['user_id']))
unset($request['user_id']);

$preferences->update($request['id'], $request);

return $this->respond('success');
}

}
