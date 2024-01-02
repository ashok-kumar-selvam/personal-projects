<?php

namespace App\Controllers\Common;

use CodeIgniter\RESTful\ResourceController;


class Common extends ResourceController
{
protected $format = 'json';



public function __construct() {
$this->questions = new \App\Models\Questions;
$this->exams = new \App\Models\Exams;
$this->answers = new \App\Models\Answers;
$this->settings = new \App\Models\Settings;
$this->members = new \App\Models\Users;
$this->db = db_connect();
$this->QuizResults = new \App\Models\QuizResults;
$this->us = new \App\Securities\UserSecurity;
$this->errors = new \App\Models\Errors;
$this->results = new \App\Models\Results;
}

    public function showResult($id = null)
    {
if(!$this->checkinfo($id, false))
return $this->checkinfo($id);
$settings = $this->settings->join('attempts', 'attempts.assign_id = settings.id')->select('settings.*')->where('attempts.id', $id)->first();
switch($settings->result_type) {
case "complete_result":
$result = $this->results->where('id', $id)->first();
$admin = $this->exams->join('users', 'users.id = exams.user_id')->select('concat(users.first_name, " ", users.last_name) as name, title, description, subject')->where('exams.id', $settings->exam_id)->first();
$show_explanation = $settings->show_explanation;

$result->answers = $this->answers->join('questions', 'questions.id = answers.question_id')->select("questions.answer as correct_answer, if(strcmp(\"$show_explanation\",\"yes\") = 0, questions.explanation, 0) as explanation, answers.*")->where('answers.result_id', $id)->orderBy('answers.number', 'asc')->findAll();
$result->admin_name = $admin->name;
break;
case "simple_result":
$result = $this->results->where('id', $id)->first();
$result->answers = [];

break;
case "pass_or_fail":
$result = $this->results->select('has_passed, (taken_points/total_points)*100 as percentage')->where('id', $id)->first();
break;
default:
$result = (object) [];
}

$result->type = $settings->result_type;
return $this->respond(['action' => 'approve', 'result' => $result]);
    }

public function checkinfo($result_id, $return = true) {
$result = $this->results->where('id', $result_id)->first();
if(!$result)
return $return ? $this->failNotFound(' Unable to find the result. Please make sure you have completed the exam. '): false;

if(!$this->us->is_user())
return $return ? $this->respond(['action' => 'login', 'reason' => 'Only logged in users can view the result. ']): false;

$user = $this->us->getData();

if($result->member_id != $user->uuid)
return $return ? $this->respond(['action' => 'reject', 'reason' => 'You are not allowed to view this result. Only the particular user can see the result']): false;

$settings = $this->settings->join('attempts', 'attempts.assign_id = settings.id')->select('settings.*')->where('attempts.id', $result_id)->first();
if(!$settings)
return $return ? $this->failNotFound(' Unable to find the proper settings for the result. Please contact the admin for more information.'): false;

if($settings->result_method == 'later' && $settings->published !=  'yes')
return $return ? $this->respond(['action' => 'reject', 'reason' => 'The admin has to publish the result. Please wait or contact your admin. ']): false;

if($settings->result_method == 'automatic' && $settings->publish_on > 0 && ($settings->publish_on/1000) > time())
return $return ? $this->respond(['action' => 'reject', 'reason' => 'The result has not been published yet. Please try again later. ']): false;

return $return ? $this->respond(['action' => 'approved', 'reason' => 'No issues so far']): true;
}

    public function createError()
    {
$rules = [
'page' => 'required',
'error' => 'required'];
$errors = [];

if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$request = (array)  $this->request->getVar();

$id = $this->errors->insert($request);
return $this->respond([
'id' => $id]);

    }

public function unsubscribe() {
$preferences = new \App\Models\EmailPreferences;
$rules = [
'id' => 'required|is_not_unique[email_preferences.id]',
];
if(!$this->validate($rules))
return $this->failValidationErrors($this->validator->getErrors());

$request = (array) $this->request->getVar();
if(isset($request['user_id']))
unset($request['user_id']);
return $this->respond($preferences->update($request['id'], $request));

}

public function anonymous_results() {

if(!$this->us->is_user())
return $this->failUnauthorised('Please login again to continue. ');
$user = $this->us->getData();
$mode = $this->request->getVar('mode');
switch($mode) {
case "exams":

$now = time();
$user_id = $user->uuid;
$where = "((settings.result_method = 'automatic' and settings.publish_on < $now) or settings.result_method = 'immediate' or settings.result_method = 'later') and settings.published = 'yes' and results.member_id = \"$user_id\"";
$results = $this->results
->join('attempts', 'attempts.id = results.id')
->join('settings', 'settings.id = attempts.assign_id')
->join('exams', 'exams.id = settings.exam_id')
->join('users', 'users.id = exams.user_id')
->select('exams.title, concat(users.first_name, " ", users.last_name) as admin_name, results.id, results.attempt, results.completed_on  ')
->where($where)
->orderBy('results.created_at', 'desc')
->findAll();
break;
default:
$results = $this->QuizResults
->join('quizzes', 'quizzes.id = quiz_results.quiz_id')
->join('users', 'users.id = quizzes.user_id')
->select(' quizzes.title, quiz_results.attempt, concat(users.first_name, " ", users.last_name) as admin_name, quiz_results.created_at as completed_on, quiz_results.id ')
->where('quiz_results.member_id', $user->uuid)
->orderBy('quiz_results.created_at', 'desc')
->findAll();

}
return $this->respond($results);
}



}
