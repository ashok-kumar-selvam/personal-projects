<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;

class Instructors extends ResourceController
{
protected $modelName = '\App\Models\Instructors';
protected $format = 'json';


public function __construct() {
$this->is = new \App\Securities\InstructorSecurity;
$this->users = new \App\Models\Users;
$this->plans = new \App\Models\Plans;
$this->us = new \App\Securities\UserSecurity;
$this->email = new \App\Controllers\Emails;

}



    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
if($this->is->is_instructor())
return $this->fail(['message' => 'You are not allowed to access this resource. ']);

return $this->respond($this->model->where('admin_id', $this->request->user->uuid)->orderBy('created_at', 'desc')->findAll());

    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {

if($this->is->is_instructor())
return $this->fail(['message' => 'You are not allowed to access this resource. ']);

$instructor = $this->model->where('id', $id)->first();
if(!$instructor)
return $this->failNotFound('Unable to find the instructor. ');

if($instructor->admin_id != $this->request->user->uuid)
return $this->fail(['message' => 'You are not allowed to access this resource. ']);

return $this->respond([
'instructor' => $instructor,
]);
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
if($this->is->is_instructor())
return $this->fail(['message' => 'You are not allowed to access this resource. ']);
$rules = [
'name' => 'required|min_length[4]|string',
'email' => 'required|is_unique[instructors.email]|valid_email',
'permissions' => 'required'];

$errors = [];

if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
if(!$this->us->check_access_count('instructors'))
return $this->fail(['message' => $this->us->getError()]);

$request = (array) $this->request->getVar();

$admin = $this->users->where('id', $this->request->user->uuid)->first();

if(!$admin)
return $this->failNotFound('Unable to find your account. ');


$name = str_replace(' ', '', $request['name']);
if(strlen($name) < 4)
return $this->fail(['message' => 'The name field should be at least 4 in length without any spaces.']);
$credentials = $this->credentials($name);
$request['user_id'] = $credentials['user_id'];
$request['password'] = $credentials['password'];
$request['admin_id'] = $admin->id;
$entity = new \App\Entities\Instructors($request);
$this->model->insert($entity);

if(isset($request['send_email']) && $request['send_email'] == 'yes') {
$this->email->sendinBlue([
'subject' => 'A new instructor account created',
'email' => $request['email'],
'message' => '',
'admin' => $admin->first_name." ".$admin->last_name,
'username' => $request['user_id'],
'password' => $request['password'],
'file' => 'welcome_instructor.php',
], true, 1);
}


return $this->respond($credentials);


    }


    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {

if($this->is->is_instructor())
return $this->fail(['message' => 'You are not allowed to access this resource. ']);

$instructor = $this->model->where('id', $id)->first();
if(!$instructor)
return $this->failNotFound('Unable to find the instructor. ');

if($instructor->admin_id != $this->request->user->uuid)
return $this->fail(['message' => 'You are not allowed to access this resource. ']);

$request = (array) $this->request->getVar();

if(isset($request['email']))
unset($request['email']);

if(isset($request['admin_id']))
unset($request['admin_id']);

if(isset($request['name']) && strlen($request['name']) < 4)
return $this->fail(['message' => 'The name field should be at least 4 in length. ']);


$entity = new \App\Entities\Instructors($request);
$this->model->update($id, $entity);
return $this->respond([
'message' => 'successfully updated'
]);

    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
if($this->is->is_instructor())
return $this->fail(['message' => 'You are not allowed to access this resource. ']);

$instructor = $this->model->where('id', $id)->first();
if(!$instructor)
return $this->failNotFound('Unable to find the instructor. ');

if($instructor->admin_id != $this->request->user->uuid)
return $this->fail(['message' => 'You are not allowed to access this resource. ']);
$this->model->where('id', $id)->delete();
return $this->respondDeleted('successfully deleted');


    }

private function credentials($name) {
helper('text');

$first = substr($name, 0, 4);
$count = 0;
$size = 9999;
while(true) {
if($count > 1000 && $count < 2000)
$size = 999999;
$last = mt_rand(1000, $size);
$user_id = $first.$last;
if(!$this->model->where('user_id', $user_id)->first())
break;
$count++;
if($count > 2000)
$size = 99999999;
}

$password = random_string('alnum', 8);
return [
'user_id' => $user_id,
'password' => $password];
}

public function activate() {
if($this->is->is_instructor())
return $this->fail(['message' => 'You are not allowed to access this resource. ']);
$rules = [
'status' => 'required|in_list[active,suspended,created]',
'id' => 'required|is_not_unique[instructors.id]'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$request = (array) $this->request->getVar();
$status = ($request['status'] == 'suspended') ? 'active': 'suspended';
$this->model->update($request['id'], ['status' => $status]);
return $this->respond([
'status' => $status]);
}



}
