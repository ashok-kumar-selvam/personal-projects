<?php

namespace App\Controllers\Users;

use CodeIgniter\RESTful\ResourceController;

class AnonymousUsers extends ResourceController
{
protected $modelName = '\App\Models\AnonymousUsers';
protected $format = 'json';

public function __construct() {
$this->quizzes = new \App\Models\Quizzes;
$this->exams = new \App\Models\Exams;
$this->anonymoususers = new \App\Models\AnonymousUsers;
$this->au = new \App\Securities\AuthSecurity;
$this->assignments = new \App\Models\Settings;
}

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
        //
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {
        //
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
$rules = [
'id' => 'required',
'name' => 'required',
'email' => 'required|valid_email|is_unique[users.email]'];
$errors = [
'email' => [
'is_unique' => 'This email id is already registered. Please login to continue. '
]
];

if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$request = (array) $this->request->getVar();

$quiz = $this->quizzes->where('id', $request['id'])->first();
$exam = $this->assignments->where('id', $request['id'])->first();
$user = $this->anonymoususers->where('email', $request['email'])->first();
unset($request['id']);

if($quiz) {
if($quiz->member_only == 'yes')
return $this->fail(['message' => ' Only registered users are allowed. Please contact the admin for more info. ']);

$request['entity'] = 'quiz';
$request['entity_id'] = $quiz->id;
} else if($exam) {
if($exam->assign_to != 'anonymous_users')
return $this->fail(['message' => ' Only registered users are allowed. Please contact the admin for more info. ']);

$request['entity'] = 'exam';
$request['entity_id'] = $exam->id;


} else {
return $this->fail(['message' => 'Invalid request. Unable to find the resource. ']);
}

$id = $user ? $user->id: $this->anonymoususers->insert($request);

$token = $this->au->shortterm_encript(['uuid' => $id, 'email' => $request['email']], true);
return $this->respond([
'token' => $token
]);

    }

    /**
     * Return the editable properties of a resource object
     *
     * @return mixed
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
        //
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        //
    }
}
