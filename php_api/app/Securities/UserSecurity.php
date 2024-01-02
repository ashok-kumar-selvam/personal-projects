<?php

namespace App\Securities;

use \App\Securities\BaseSecurity;
use \App\Securities\AuthSecurity;

class UserSecurity extends BaseSecurity 
{
public $access = [
'basic' => [
'instructors' => 0,
'groups' => 2,
'temporary_members' => 0,
'result_analyse' => false,
'single_member' => false,
'allowed_emails' => false,
'auto_publish' => false,
'question_time' => false,
'show_explanation' => false,
'excel_upload' => false,
'email_notifications' => false,
],
'silver' => [
'instructors' => 0,
'groups' => 10,
'temporary_members' => 50,
'result_analyse' => true,
'single_member' => true,
'allowed_emails' => false,
'auto_publish' => false,
'question_time' => true,
'show_explanation' => true,
'excel_upload' => false,
'email_notifications' => false,
],

'gold' => [
'instructors' => 2,
'groups' => 25,
'temporary_members' => 100,
'result_analyse' => true,
'single_member' => true,
'allowed_emails' => true,
'auto_publish' => true,'question_time' => true,
'show_explanation' => true,
'excel_upload' => true,
'email_notifications' => true,
],
];

public function is_user() {
return $this->is_registered_user() || $this->is_anonymous_user();
}

public function is_registered_user() {
$as = new AuthSecurity();
$user = $as->longterm_decript();
if($user === false) {
$this->setError(' it is not registered user');
return false;
}

$query = $this->db->table('users')->where('id', $user['uuid'])->get();
$result = $query->getRow();
if(!$result) {
$this->setError(' the query returned null in the result ');

return false;
}
return true;
}

public function is_anonymous_user() {
$as = new AuthSecurity();
$user = $as->shortterm_decript();
if(!$user) {
$this->setError($as->getError());
return false;
}

$query = $this->db->table('anonymous_users')->where('id', $user['uuid'])->get();
$result = $query->getRow();
if(!$result) {
$this->setError(' the query returned false in anonymous result ');
return false;
}
return true;
}

public function is_member($user_id) {
$as = new AuthSecurity();
$user = $as->longterm_decript();

if(!$user)
return false;

$query = $this->db->table('user_members')->where('user_id', $user_id)->where('member_id', $user['uuid'])->where('status', 'approved')->get();
$result = $query->getRow();
if(!$result)
return false;
return true;
}


public function getData() {
$as = new AuthSecurity();
$user = $as->longterm_decript();
if(!$user)
$user = $as->shortterm_decript();
return (object) $user;
}

public function is_owner($id, $entity) {
    $cacheKey = 'is_owner_' . $entity . '_' . $id . '_' . $this->request->user->uuid;
    if ($result = cache()->get($cacheKey)) {
        return $result;
    }

//$user = $this->getData();
$db = db_connect();
switch($entity) {
case "exams":
$query = $db->table('exams')->where('id', $id)->get();
$result = $query->getRow();
break;
case "assignments":
$query = $db->table('settings')->join('exams', 'exams.id = settings.exam_id')->select('exams.user_id')->where('settings.id', $id)->get();
$result = $query->getRow();

break;
case "quizzes":
$query = $db->table('quizzes')->where('id', $id)->get();
$result = $query->getRow();

break;
case "groups":
$query = $db->table('groups')->where('id', $id)->get();
$result = $query->getRow();
break;
case "questions":
$query = $db->table('questions')->where('id', $id)->get();
$result = $query->getRow();
break;
case "members":
$query = $db->table('user_members')->where('member_id', $id)->where('user_id', $this->request->user->uuid)->get();
$result = $query->getRow();

break;
}
if(!$result)
return false;
if($result->user_id != $this->request->user->uuid) {
if(!$this->request->user->admin_id || $this->request->user->admin_id != $result->user_id)
return false;
}
    cache()->save($cacheKey, true, 86400); // Cache for 60 seconds
 return true;
}

public function check_access_assignments(array $request) {
$plan = $this->plans->getActivePlan($this->request->user->admin_id ?? $this->request->user->uuid);
if(!$plan)
return $this->setError('unable to find the plan');
if(!isset($this->access[$plan]))
return $this->setError(' Unable to find this plan.');

$access = $this->access[$plan];

foreach($request as $key => $value) {
switch($key) {
case "assign_to":
$key = $value;
break;
case "result_method":
if($value == 'automatic')
$key = 'auto_publish';
break;
case "show_explanation":
$value = $value == 'no' ? 0: $value;
}

if(isset($access[$key]) && $access[$key] == false && !empty($value))
return $this->setError(" The option $key with the value $value is not allowed in $plan plan. Please subscribe to some other plan to enjoy this feature.");
}
return true;
}

public function check_access_count($item) {
$user_id = $this->request->user->admin_id ?? $this->request->user->uuid;

$plan = $this->plans->getActivePlan($user_id);
if(!$plan)
return $this->setError('unable to find the plan');
if(!isset($this->access[$plan]))
return $this->setError(' Unable to find this plan.');
$access = $this->access[$plan];
$count = $this->db->table($item)->where('user_id', $user_id)->countAllResults();

if(isset($access[$item]) && $count >= $access[$item])
return $this->setError(" You can only create $count $item with $plan plan. ");
return true;
}

}
