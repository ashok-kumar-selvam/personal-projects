<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;

class Quizzes extends ResourceController
{
protected $modelName = '\App\Models\Quizzes';
protected $format = 'json';

public function __construct() {
$this->questions = new \App\Models\Questions;


$this->results = new \App\Models\Results;
$this->us = new \App\Securities\UserSecurity;
$this->quizresults = new \App\Models\QuizResults;
$this->is = new \App\Securities\InstructorSecurity;
}

    public function index()
    {

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('quizzes', 'view'))
return $this->fail(['message' => $this->is->getError()]);
$quizzes = $this->model->where('user_id', $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid)->orderBy('created_at', 'desc')->findAll();
foreach($quizzes as $quiz) {
$quiz->questions = $this->questions->join('question_sheet', 'questions.id = question_sheet.question_id')->where('question_sheet.entity_id', $quiz->id)->countAllResults();
}

return $this->respond($quizzes);
    }

    public function show($id = null)
    {

if(!$this->us->is_owner($id, 'quizzes'))
return $this->failUnauthorized('You are not allowed to access this resource. ');

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('quizzes', 'view'))
return $this->fail(['message' => $this->is->getError()]);

$quiz = $this->model->where('id', $id)->first();
if(!$quiz)
return $this->failNotFound('Unable to find the quiz');

$questions = $this->questions->join('question_sheet', 'questions.id = question_sheet.question_id')->select('questions.*')->where('question_sheet.entity_id', $id)->orderBy('questions.id', 'asc')->findAll();
return $this->respond([
'quiz' => $quiz,
'questions' => $questions]);
    }
    public function create()
    {

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('quizzes', 'create'))
return $this->fail(['message' => $this->is->getError()]);

$rules = [
'title' => 'required',
'category' => 'if_exist|string',
'notes' => 'if_exist|string',
'validity' => 'required|in_list[always,untill]',
'date' => 'if_exist|valid_date',
'member_only' => 'if_exist|in_list[yes,no]',
'questions' => 'permit_empty|if_exist|in_ext[questions,xlsx,xls,json,xml,docx,txt,toml]',
];


$errors = [
'questions' => [
'in_ext' => 'The file type is not supported'
],
];

if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$file = $this->request->getFile('questions');

if($file->isValid()) {
$validator = new \App\Libraries\Uploads\Validator($file, 'quiz');

if(!$validator->run()) 
return $this->fail([
'message' => $validator->getError()]);


if($validator->getCount() > 30)
return $this->fail(['message' => 'You can only upload 30 questions to quiz. If you need more questions, Please try exams.']);

}

$request = (array) $this->request->getVar();
$request['user_id'] = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;

$request['expires_on'] = ($request['validity'] == 'untill') ? strtotime($request['date']): 0;
if(!isset($request['member_only']))
$request['member_only'] = 'no';
$quiz = new \App\Entities\Quizzes($request);
$quiz_id = $this->model->insert($quiz);

if($file->isValid())
$this->questions->upload($validator->getQuestions(), $quiz_id, $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid);

return $this->respond([
'id' => $quiz_id]);
    }


    public function update($id = null)
    {

if(!$this->us->is_owner($id, 'quizzes'))
return $this->fail(['message' => ' You cannot modify this resource. ']);

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('quizzes', 'edit'))
return $this->fail(['message' => $this->is->getError()]);

$rules = [
'id' => 'if_exist|is_owner[id,quizzes]|is_not_unique[quizzes.id]',
'user_id' => 'if_exist|is_not_unique[users.id]',
'title' => 'if_exist|string',
'category' => 'if_exist|string',
'notes' => 'if_exist|string',
'validity' => 'if_exist|in_list[always,untill]',
'member_only' => 'if_exist|in_list[yes,no]',
'changed' => 'required',
];

$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());



$request = (array) $this->request->getVar();

if(isset($request['id']))
unset($request['id']);
if(isset($request['user_id']))
unset($request['user_id']);
unset($request['expires_on']);

$changed = (array) $request['changed'];
if($changed['validity'] || $changed['date']) {
$request['expires_on'] = ($request['validity'] == 'untill') ? strtotime($request['date']): 0;
if($request['validity'] == 'untill' && $request['expires_on'] < time())
return $this->fail(['message' => ' Invalid date. The date and time should be greater than current date and time. ']);

}

if($changed['member_only'])
$request['member_only'] = (isset($request['member_only'])) ? $request['member_only']: 'no';


$quiz = $this->model->where('id', $id)->first();

if(!$quiz)
return $this->failNotFound(' Unable to find the resource. ');

$this->model->update($id, $request);
return $this->respond([
'id' => $id]);

    }

public function publish($id) {
if(!$this->us->is_owner($id, 'quizzes'))
return $this->failUnauthorized(' you are not allowed to access this resource. ');
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('quizzes', 'edit'))
return $this->fail(['message' => $this->is->getError()]);


$rules = [
'publish' => 'required|in_list[no,yes]'];
$quiz = $this->model->where('id', $id)->first();
if(!$quiz)
return $this->failNotFound(' Unable to find the quiz. ');


$request = (array) $this->request->getVar();
$this->model->update($id, ['publish' => $request['publish']]);
return $this->respond(['id' => $id]);

}


    public function delete($id = null)
    {

if(!$this->us->is_owner($id, 'quizzes'))
return $this->fail(['message' => 'You cannot delete this quiz.']);
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('quizzes', 'delete'))
return $this->fail(['message' => $this->is->getError()]);

$quiz = $this->model->where('id', $id)->first();
if(!$quiz)
return $this->failNotFound(' Unable to find the resource. ');
db_connect()->table('question_sheet')->where('entity_id', $id)->delete();

return $this->respondDeleted($this->model->where('id', $id)->delete());

    }




public function all_results($id) {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('quizzes', 'view'))
return $this->fail(['message' => $this->is->getError()]);

$results = $this->quizresults->join('users', 'users.id = quiz_results.member_id', 'left')->join('anonymous_users', 'anonymous_users.id = quiz_results.member_id', 'left')
->join('quizzes', 'quizzes.id = quiz_results.quiz_id')
->select(' ifnull(anonymous_users.name, concat(users.first_name, " ", users.last_name)) as name, quizzes.title, quiz_results.attempt, quiz_results.member_type, quiz_results.created_at, quiz_results.id ')
->where('quizzes.user_id', $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid)
->where('quizzes.id', $id)
->orderBy('quiz_results.created_at', 'desc')
->findAll();


return $this->respond($results);
}

public function view_result($id) {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('quizzes', 'view'))
return $this->fail(['message' => $this->is->getError()]);

$result = $this->quizresults->join('users', 'users.id = quiz_results.member_id', 'left')->join('anonymous_users', 'anonymous_users.id = quiz_results.member_id', 'left')
->join('quizzes', 'quizzes.id = quiz_results.quiz_id')
->select('ifnull(anonymous_users.name, concat(users.first_name, " ", users.last_name)) as name, quizzes.title, quiz_results.* ')
->where('quizzes.user_id', $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid)
->where('quiz_results.id', $id)
->first();
if(!$result)
return $this->failNotFound('Unable to find the result. ');


return $this->respond($result);
}

}
