<?php

namespace App\Controllers\Common;

use CodeIgniter\RESTful\ResourceController;
use \App\Securities\UserSecurity;

class Quizzes extends ResourceController
{
protected $modelName = '\App\Models\Quizzes';
protected $format = 'json';

public function __construct() {
$this->questions = new \App\Models\Questions;
$this->results = new \App\Models\QuizResults;
$this->usersecurity = new UserSecurity();
$this->anonymoususers = new \App\Models\AnonymousUsers;
$this->authsecurity = new \App\Securities\AuthSecurity;
$this->users = new \App\Models\Users;
}

    public function show($id = null)
    {

if(!$this->checkinfo($id, false))
return $this->checkinfo($id, true);

$quiz = $this->model->join('users', 'users.id = quizzes.user_id')->select('quizzes.*, concat(users.first_name, " ", users.last_name) as owner')->where('quizzes.id', $id)->first();
$questions = $this->questions->join('question_sheet', 'questions.id = question_sheet.question_id')->select('questions.id, questions.type, questions.question, questions.options')->where('question_sheet.entity_id', $id)->findAll();

return $this->respond([
'quiz' => $quiz,
'questions' => $questions]);

    }

public function save() {
$rules = [
'quiz_id' => 'required|is_not_unique[quizzes.id]',
'time' => 'required',
'questions' => 'required'];

$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();
$quiz = $this->model->where('id', $request['quiz_id'])->first();

$stats = [
'quiz_id' => $quiz->id,
'total_time' => $request['time'],
'total_questions' => $this->questions->join('question_sheet', 'questions.id = question_sheet.question_id')->where('question_sheet.entity_id', $quiz->id)->countAllResults(),];


$user = [];
if($this->usersecurity->is_member($quiz->user_id)) {
$stats['member_type'] = 'member';
$user = $this->authsecurity->longterm_decript();
} else if($this->usersecurity->is_registered_user()) {
$stats['member_type'] = 'registered';
$user = $this->authsecurity->longterm_decript();
} else if($this->usersecurity->is_anonymous_user()) {
$stats['member_type'] = 'anonymous';
$user = $this->authsecurity->shortterm_decript();
} else {
return $this->fail([
'message' => ' Your validation has expired. Please try again. If it continues, contact the admin. ']);

}

if(!$user)
return $this->fail(['message' => ' Your session has expired. Please start again. ']);

$stats['member_id'] = $user['uuid'];
$stats['attempt'] = $this->results->where('quiz_id', $quiz->id)->where('member_id', $stats['member_id'])->countAllResults()+1;

$stats2 = $this->correction($request['questions']);
$stats = array_merge($stats, $stats2);
$stats['result']['quiz'] = $quiz;


$entity = new \App\Entities\QuizResults($stats);
$id = $this->results->insert($entity);
return $this->respond([
'id' => $id]);

}

public function result($id) {
$result = $this->results->where('id', $id)->first();
if(!$result)
return $this->failNotFound('Unable to find the result');

if(!$this->usersecurity->is_user())
return $this->fail(' Your session has expired. ');

if($result->member_type == 'member' || $result->member_type == 'registered') {
$names = (object) $this->users->select('concat(first_name, " ", last_name) as name')->where('id', $result->member_id)->first();
$result->name = $names->name;

} else {
$names = (object) $this->anonymoususers->where('id', $result->member_id)->first();
$result->name =  $names->name;
}



return $this->respond($result);

}

public function checkinfo($id, $res = true) {
$quiz = $this->model->where('id', $id)->first();
if(!$quiz)
return ($res === true) ? $this->failNotFound(' Unable to find the quiz. '): false;
if($quiz->publish == 'no')
return ($res === true) ? $this->fail(['message' => 'Invalid request. Unable to find the quiz']): false;

if($quiz->expires_on != 0 && $quiz->expires_on < time())
return ($res === true) ? $this->fail([ 'message' => ' The quiz is expired. ']): false;

$questionCount = $this->questions->join('question_sheet', 'questions.id = question_sheet.question_id')->where('question_sheet.entity_id', $id)->countAllResults();
if($questionCount <= 0)
return ($res === true) ? $this->fail(['message' => ' Invalid quiz. This quiz has no questions. Please check with the admin. ']): false;


// check whether it is a user or not
if(!$this->usersecurity->is_user()) {
$action = 'register'; // if it is not a user he/she must register to access this quiz

if($quiz->member_only == 'yes')
$action = 'login'; // if it is a protected quiz, there is no meaning in registering in the website. because this quiz is only ment for approved members. so ask them to login to check whether he/she is a member or not

return ($res === true) ? $this->respond([
'user' => 'anonymous',
'message' => $this->usersecurity->getError(),
'action' => $action]): false;
}

/*
They have come to this place because:
1. they have already logged in and they are clicking the link from their dashboard.
2. they have already got anonymous user registration and accessing this quiz after attending the previous quiz.
3. they have just registered in the site and they are trying to attend the quiz.
*/

if($quiz->member_only == 'yes') { // check whether it is a protected quiz if yes,

if(!$this->usersecurity->is_member($quiz->user_id)) { // check whether the user is a member of this admin. if no,
$action = 'getRegistered'; // if anonymous user
if($this->usersecurity->is_registered_user()) // check whether the user is registered or anonymously registered. if registered,
$action = 'getApproved'; // the user has to get approval to the admin. 

return ($res === true) ? $this->respond(['user' => 'nonmember', 'action' => $action, 'message' => 'This is a protected quiz. You need to ask approval from the admin of this quiz. ']): false;
} else {
return ($res === true) ? $this->respond(['user' => 'member', 'action' => 'approve']): true;
}

} else {
if($this->usersecurity->is_registered_user()) 
return ($res === true) ? $this->respond(['user' => 'registered', 'action' => 'approve']): true;
if($this->usersecurity->is_anonymous_user())
return ($res === true) ? $this->respond(['user' => 'anonymous', 'action' => 'approve']): true;

}
return ($res === true) ? $this->fail(['message' => 'Unknown error occured']): false;
}

public function register() {
$rules = [
'id' => 'required|is_not_unique[quizzes.id]',
'name' => 'required|string',
'email' => 'required|valid_email'];

$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$request = (array) $this->request->getVar();
$quiz = $this->model->where('id', $request['id'])->first();

if($quiz->member_only == 'yes')
return $this->fail(['message' => ' Only registered users are allowed. Please contact the admin for more info. ']);

$user = $this->anonymoususers->where('email', $request['email'])->first();
if($user) {
$this->anonymoususers->update($user->id, ['name' => $request['name']]);
$id = $user->id;
} else {
$request['entity'] = 'quiz';
$request['entity_id'] = $request['id'];
unset($request['id']);

$id = $this->anonymoususers->insert($request);
}
if(!isset($request['remember']))
$request['remember'] = false;

$token = $this->authsecurity->shortterm_encript(['uuid' => $id], $request['remember']);
return $this->respond([
'token' => $token]);

}

private function correction($questions) {
$stats = [
'attempted_questions' => 0,
'answered_questions' => 0,
'correct_answers' => 0];
foreach($questions as $question) {
$q = (object) $question;
$a = $this->questions->where('id', $q->id)->first();

if(!$a) {
throw new \Exception("There is some error in the question with id number ".$q->id.". Please contact the admin.");
continue;
}


if($q->attempted)
$stats['attempted_questions']++;

if($q->answer != '__None__')
$stats['answered_questions']++;

switch($q->type) {
case 'single_choice':
case 'true_or_false':
if($q->answer === $a->answer) {
$stats['correct_answers']++;
$question->is_correct = true;
} else if($q->answer != '__None__') {
$question->is_correct = false;
}

$question->correct = $a->answer;
break;
case 'multi_choice':
if($q->answer == '__None__') {
$question->correct = $a->answer;

continue 2;

}
$array1 = $q->answer;
$array2 = $a->answer;
sort($array1);
sort($array2);
if($array1 == $array2) {
$stats['correct_answers']++;
$question->is_correct = true;

} else {
$question->is_correct = false;
}

$question->correct = $array2;
break;
}

$question->point = (isset($question->is_correct) && $question->is_correct == true) ? $a->point: ($a->mpoint > 0 ? ($a->point-$a->mpoint): 0);

} // end of foreach

$stats['result'] = [
'questions' => $questions];

return $stats;
} // end of method


}
