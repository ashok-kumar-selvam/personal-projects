<?php

namespace App\Securities;

use \App\Securities\BaseSecurity;
use \App\Securities\AuthSecurity;

class InstructorSecurity extends BaseSecurity 
{
public function __construct() {
parent::__construct();
$this->us = new \App\Securities\UserSecurity;
$this->instructors = new \App\Models\Instructors;
$this->users = new \App\Models\Users;
}

public function has_access($entity, $action) {
$cacheKey = 'has_access_' . $entity . '_' . $action . '_' . $this->request->user->uuid;
$cacheValue = cache()->get($cacheKey);
if($cacheValue !== null)
return $cacheValue;

$userData = $this->us->getData();

$instructor = $this->instructors->where('id', $userData->uuid)->first();
if(!$instructor) {
$this->setError('Unable to find the instructor account.');
return false;
}
if($instructor->admin_id != $userData->admin_id) {
$this->setError(' Invalid admin. ');
return false;
}

if($instructor->status == 'suspended') {
$this->setError(' account suspended. ');
return false;
}

if(!$this->users->where('id', $instructor->admin_id)->where('type', 'admin')->where('status', 'approved')->first()) {
$this->setError('Invalid admin account. ');
return false;
}

if(!isset($instructor->permissions)) {
$this->setError(' You have no permissions. ');
return false;
}

$permissions = (array) $instructor->permissions;
foreach($permissions as $key => $value)
$permissions[$key] = (array) $permissions[$key];

if(!isset($permissions[$entity])) {
$this->setError(" You have no permission to access $entity ");
return false;
}

if(!isset($permissions[$entity][$action]) || $permissions[$entity][$action] != true) {
$this->setError(" You have no permission to $action the $entity. ");
return false;

}

cache()->save($cacheKey, true, 900);

return true;
}

public function is_instructor() {
$cacheKey = $this->request->user->uuid;
$cacheValue = cache()->get($cacheKey);
if($cacheValue !== null)
return $cacheValue;



$userData = $this->us->getData();
$instructor = $this->instructors->where('id', $userData->uuid)->first();
if(!$instructor) {
cache()->save($cacheKey, false, 900);
return false;
}

cache()->save($cacheKey, true, 900);
return true;

}


}
