<?php

namespace App\Controllers\Users;
use Firebase\JWT\JWT;

use CodeIgniter\RESTful\ResourceController;

class Instructors extends ResourceController
{
protected $modelName = '\App\Models\Instructors';
protected $format = 'json';

public function __construct() {
helper('Auth');
$this->keys = new \App\Models\Keys;
$this->email = new \App\Controllers\Emails;
$this->users = new \App\Models\Users;
$this->model = new \App\Models\Instructors;
$this->format = 'json';
}

public function getInstructor($user_id) {

return $this->model->where('user_id', $user_id)->first();
}





}
