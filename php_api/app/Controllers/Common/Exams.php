<?php

namespace App\Controllers\Common;

use CodeIgniter\RESTful\ResourceController;

class Exams extends ResourceController
{
protected $modelName = 'App\Models\Settings';
protected $format = 'json';

public function __construct() {
helper('Notifications');
$this->exams = new \App\Models\Exams;
$this->questions = new \App\Models\Questions;
$this->group_members = new \App\Models\Admin\GroupMembers;
$this->us = new \App\Securities\UserSecurity;
$this->authsecurity = new \App\Securities\AuthSecurity;
$this->anonymoususers = new \App\Models\AnonymousUsers;
$this->attempts = new \App\Models\Attempts;
$this->answers = new \App\Models\Answers;
$this->user_members = new \App\Models\Admin\UserMembers;
$this->db = \Config\Database::connect();
$this->users = new \App\Models\Users;
$this->results = new \App\Models\Results;
$this->credits = new \App\Credits\Credits;
$this->qsheet = new \App\Models\QuestionSheet;
$this->corrections = new \App\Libraries\Corrections\Corrections;

}

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */

    public function show($id = null)
    {
if(!$this->checkinfo($id, false))
return $this->checkinfo($id);

$assign = $this->model->join('exams', 'exams.id = settings.exam_id')->select('settings.*, exams.title, exams.subject, exams.description as message')->where('settings.id', $id)->first(); 

if(!$assign)
return $this->failNotFound('Unable to find the exam');
$assign->questions = $this->questions->join('question_sheet', 'questions.id = question_sheet.question_id')->where('question_sheet.entity_id', $assign->exam_id)->countAllResults();
$user = $this->us->getData();
$assign->attempt = $this->db->table('attempts')->where('member_id', $user->uuid)->where('assign_id', $id)->countAllResults()+1;
$assign->points = $this->qsheet->getPoints($assign->exam_id);
return $this->respond([
'exam' => $assign]);

    }


public function checkinfo($id, $return = true) {
$assign = $this->model->where('id', $id)->first();
if(!$assign)
return $return ? $this->failNotFound('Unable to find the exam.'): false;

$exam = $this->exams->where('id', $assign->exam_id)->first();
helper('Credit');
$remaining_credits = get_remaining_credits($exam->user_id);
if($remaining_credits <= 0)
return $return ? $this->fail(['message' => 'There is no remaining credits with your admin. Please inform it to your admin. ']): false;

if($assign->is_active != 'yes')
return $return ? $this->fail(['message' => 'Invalid request']): false;

switch($assign->assign_to) {
case "single_member":
if(!$this->us->is_user())
return $return ? $this->respond(['user' => 'anonymous', 'action' => 'login', 'message' => 'Please login to continue']): false;

if(!$this->us->is_member($exam->user_id))
return $return ? $this->respond(['user' => 'anonymous', 'action' => 'getApproval', 'message' => 'Please get approval from the admin. ']): false;
$user = $this->us->getData();


if($assign->assignee_id != $user->uuid)
return $return ? $this->fail(['message' => 'Sorry, This link was only generated for someother user. ']): false;


break;
case "all_members":
if(!$this->us->is_user())
return $return ? $this->respond(['user' => 'anonymous', 'action' => 'login', 'message' => 'This exam is only for registered and approved members. ']): false;

if(!$this->us->is_member($exam->user_id))
return $return ? $this->respond(['user' => 'registered', 'action' => 'getApproval', 'message' => 'Sorry, This exam is only for approved members. ']): false;

break;
case "single_group":

if(!$this->us->is_user())
return $return ? $this->respond(['user' => 'anonymous', 'action' => 'login', 'message' => 'This exam is only for the registered users. ']): false;

if(!$this->us->is_member($exam->user_id))
return $return ? $this->respond(['user' => 'registered', 'action' => 'getApproval', 'message' => ' This exam is only for particular approved users. Please get approval from your admin.']): false;

$user = $this->us->getData();
if(!$this->group_members->where('group_id', $assign->assignee_id)->where('member_id', $user->uuid)->first())
return $return ? $this->respond(['user' => 'member', 'action' => 'getApproval', 'message' => 'This exam is available only for a group of members. Please contact your admin for more details. ']): false;


break;
case "anonymous_users":

if($assign->allowed_access > 0 && $this->db->table('attempts')->groupBy('member_id')->where('assign_id', $assign->id)->countAllResults() >= $assign->allowed_access)
return $return ? $this->respond(['user' => 'anonymous', 'action' => 'reject', 'message' => 'Sorry, you are late. The limit has been exceeded. Please contact your admin for more information. ']): false;


if(!$this->us->is_user())
return $return ? $this->respond(['user' => 'anonymous', 'action' => 'register', 'message' => 'Please register to continue. ']): false;

if($this->us->is_anonymous_user() && !is_null($assign->allowed_emails) && count($assign->allowed_emails) > 0) {

$data = $this->us->getData();
if(!in_array($data->email, $assign->allowed_emails))
return $return ? $this->respond(['user' => 'anonymous', 'action' => 'reject', 'message' => 'Sorry, This exam is only for certain users. Please contact your admin for more info.']): false;



}


break;
default:
return $return ? $this->fail(['message' => 'Invalid request.']): false;
}

$additionals = new \App\Models\Additional;
$data = $this->us->getData();
$additional = $additionals->where('assign_id', $assign->id)->where('member_id', $data->uuid)->select('sum(time) as time, sum(attempt) as attempt')->first();

$allowed_attempts = $assign->allowed_attempts;
if($additional)
$allowed_attempts = $assign->allowed_attempts+$additional->attempt;

if(!$this->is_allowed_attempts($assign->id, $allowed_attempts))
return $return ? $this->respond(['user' => 'approved', 'action' => 'reject', 'message' => 'You have no remaining attempts']): false;

if($assign->start_time > 0 && ($assign->start_time/1000) > time()) 
return $return ? $this->respond(['user' => 'approved', 'action' => 'wait', 'time' => $assign->start_time-(time()*1000), 'message' => 'Please wait the exam did not start']): false;

if($assign->end_time > 0 && ($assign->end_time/1000) < time())
return $return ? $this->fail(['message' => 'The exam has ended. ']): false;

if($return) {
if($assign->resumable == 'yes' && $this->is_resumable($assign->id)) {
$attempt = $this->attempts->where('assign_id', $assign->id)->where('member_id', $data->uuid)->where('status', 'started')->orderBy('created_at', 'desc')->first();
return $this->respond(['user' => 'approved', 'action' => 'approve', 'resumable' => 'yes', 'attempt' => $attempt]);

}

}

return $return ? $this->respond(['user' => 'approved', 'action' => 'approve', 'resumable' => 'no']): true;
}

public function is_allowed_access($id, $access) {

return $this->db->table('attempts')->distinct('member_id')->where('assign_id', $id)->countAllResults() < $access;

}

public function is_allowed_attempts($id, $attempt, $context = 'start') {
$attempts = new \App\Models\Attempts;
$data = $this->us->getData();
if($attempt == 0)
return true;
$attempted = $attempts->where('assign_id', $id)->where('member_id', $data->uuid)->countAllResults();
if($attempted < $attempt)
return true;

if($attempted == $attempt && $context == 'start')
return false;

if($attempted == $attempt && $context == 'resume')
return true;
}

public function register() {
$rules = [
'id' => 'required|is_not_unique[settings.id]',
'name' => 'required|string',
'email' => 'required|valid_email'];

$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$request = (array) $this->request->getVar();
$assign = $this->model->where('id', $request['id'])->first();

if($assign->assign_to != 'anonymous_users')
return $this->fail(['message' => ' Only registered users are allowed. Please contact the admin for more info. ']);

$user = $this->anonymoususers->where('email', $request['email'])->first();
if($user) {
$this->anonymoususers->update($user->id, ['name' => $request['name']]);
$id = $user->id;
} else {
$request['entity'] = 'exam';
$request['entity_id'] = $assign->exam_id;
unset($request['id']);

$id = $this->anonymoususers->insert($request);
}
if(!isset($request['remember']))
$request['remember'] = false;

$token = $this->authsecurity->shortterm_encript(['uuid' => $id, 'email' => $request['email']], $request['remember']);
return $this->respond([
'token' => $token]);

}

public function start($id) {
if(!$this->checkinfo($id, false))
return $this->checkinfo($id);
$assign = $this->model->join('exams', 'exams.id = settings.exam_id')->select('exams.user_id, settings.*')->where('settings.id', $id)->first();

$questions = $this->questions->join('question_sheet', 'questions.id = question_sheet.question_id')->select('questions.id as question_id, questions.question, questions.options, questions.type, questions.point, questions.mpoint, questions.answer')->where('question_sheet.entity_id', $assign->exam_id)->findAll();
$userData = $this->us->getData();
$attempt = $this->attempts->where('assign_id', $assign->id)->where('member_id', $userData->uuid)->countAllResults()+1;
$point = 0;
foreach($questions as $question) {
$point += $question->point;
}

$this->attempts->where('status', 'started')->where('assign_id', $id)->where('member_id', $userData->uuid)->set('status', 'interrupted')->update();


$result_id = $this->attempts->insert([
'assign_id' => $id,
'member_id' => $userData->uuid,
'attempt' => $attempt,
'total_points' => $point]);
$credit_id = $this->credits->getCreditId($assign->user_id);
if(!$credit_id)
return $this->fail(['message' => 'There are some problem with credits. Please check with your admin.']);

$this->db->table('credits_spent')->insert([

'date' => time(),
'user_id' => $assign->user_id,
'exam_id' => $assign->exam_id,
'credit_id' => $credit_id,
'assign_id' => $id,
'member_id' => $userData->uuid,
]);

$last = count($questions)-1;
if($assign->question_random == 'yes')
shuffle($questions);

foreach($questions as $index => $question) {
$q = $question->toArray();
$q['result_id'] = $result_id;
$q['number'] = $index+1;
$q['time'] = 0;
$q['has_attempted'] = 'no';
$q['has_answered'] = 'no';
$q['has_marked'] = 'no';
$q['point'] = 0;
if($q['type'] == 'match_it') {
$answers = array_column($question->answer, 'answer');
shuffle($answers);
$q['options'] = [];
foreach($question->answer as $key => $obj) {
array_push($q['options'], [
'question' => $obj->question,
'answer' => $answers[$key]]);

}

} else if($q['type'] == 'single_choice' || $q['type'] == 'multi_choice' || $q['type'] == 'true_or_false') {
if($assign->option_random == 'yes')
shuffle($q['options']);
}


$q['answer'] = '';
if($q['type'] == 'fill_the_blanks' || $q['type'] == 'multi_choice')
$q['answer'] = [];
if($q['type'] == 'match_it')
$q['answer'] = $q['options'];
if($q['type'] == 'descriptive')
$q['answer'] = ['text' => '', 'file' => ['id' => null, 'name' => null]];

if($q['type'] == 'single_choice' || $q['type'] == 'true_or_false')
$q['answer'] = '';

$q['has_next'] = ($index < $last) ? true: false;
$q['has_previous'] = ($index > 0) ? true: false;

$entity = new \App\Entities\Answers($q);
$this->answers->insert($entity);

}

$question = $this->answers->join('questions', 'questions.id = answers.question_id')->select('questions.point as given_point, answers.*')->where('result_id', $result_id)->orderBy('number', 'asc')->first();
if(!$question)
return $this->fail(['message' => 'Unexpected error occured. Unable to find questions. Please report it to your admin.']);
$this->db->table('attend')->insert(['result_id' => $result_id]);

$question->has_attempted = 'yes';

$settings = $this->model->join('attempts', 'attempts.assign_id = settings.id')->join('exams', 'exams.id = settings.exam_id')->join('attend', 'attend.result_id = attempts.id')->select(' settings.time_limit * (60) as total_time, settings.question_time, attend.time as taken_time, attempts.id, attempts.attempt, exams.title')->where('attempts.id', $result_id)->first();
$settings->total_questions = count($questions);
$additionals = new \App\Models\Additional;
$additional = $additionals->where('assign_id', $assign->id)->where('member_id', $userData->uuid)->select('sum(time) as time')->first();

if($additional)
$settings->total_time = $settings->total_time+$additional->time;

return $this->respond([
'question' => $question,
'settings' => $settings]);

}

public function is_resumable($id) {
$data = $this->us->getData();
$attempt = $this->attempts->where('assign_id', $id)->where('member_id', $data->uuid)->where('status', 'started')->orderBy('created_at', 'desc')->first();
if(!$attempt)
return false;
return true;
}

public function resume($id) {
if(!$this->checkinfo($id, false))
return $this->checkinfo($id);
$data = $this->us->getData();

$attempt = $this->attempts->where('assign_id', $id)->where('member_id', $data->uuid)->where('status', 'interrupted')->orderBy('created_at', 'desc')->first();
if(!$attempt)
return $this->fail(['message' => 'Invalid request. Unable to find the attempt']);
$question = $this->answers->join('questions', 'questions.id = answers.question_id')->select('questions.point as given_point, answers.*')->where('result_id', $attempt->id)->where('has_attempted', 'yes')->orderBy('updated_at', 'desc')->first();
if(!$question)
$question = $this->answers->where('result_id', $attempt->id)->orderBy('number', 'asc')->first();
if(!$question)
return $this->fail(['message' => 'Invalid request. Unable to find the last attempted question. ']);
$question->has_attempted = 'yes';
$settings = $this->model->join('exams', 'exams.id = settings.exam_id')->join('attempts', 'attempts.assign_id = settings.id')->join('attend', 'attend.result_id = attempts.id')->select('exams.title, settings.time_limit * (60) as total_time, settings.question_time, attend.time as taken_time, attempts.attempt, attempts.id')->where('attempts.id', $attempt->id)->first();
$settings->total_questions = $this->answers->where('result_id', $attempt->id)->countAllResults();

return $this->respond([
'settings' => $settings,
'question' => $question]);

}

public function finish() {
$rules = [
'result_id' => 'required|is_not_unique[attempts.id]',
'time' => 'required|numeric'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();
$status = $this->corrections->run($request['result_id']);
if(!$status)
return $this->fail(['message' => $this->corrections->getError()]);

$saved = $this->setResult($request['result_id'], $status, $request['time']);

$this->attempts->where('id', $request['result_id'])->set('status', 'completed')->update();
$result = $this->attempts->join('settings', 'attempts.assign_id = settings.id')->select('settings.result_method, settings.publish_on, settings.exam_id, attempts.member_id, attempts.attempt, attempts.id as result_id, settings.published, settings.message as result_message')->where('attempts.id', $request['result_id'])->first();
if(!$result)
return $this->failNotFound("Unable to find the result. Don't worry. your answers are safe.");

$exam = $this->exams->where('id', $result->exam_id)->first();
$user = $this->users->select(' concat(first_name, " ", last_name) as name ')->where('id', $result->member_id)->first() ?? $this->anonymoususers->where('id', $result->member_id)->first();
notify($exam->user_id, " $user->name has completed the attempt $result->attempt in $exam->title . ");
return $this->respond($result);
} // end of method

public function correction($result_id) {
$status = 'completed';

$answers = $this->answers->where('result_id', $result_id)->where('has_attempted', 'yes')->orderBy('number', 'asc')->findAll();
$assign = $this->attempts->join('settings', 'settings.id = attempts.assign_id')->where('attempts.id', $result_id)->first();

foreach($answers as $answer) {
$question = $this->questions->where('id', $answer->question_id)->first();
$correct = false;
switch($question->type) {
case "single_choice":
case "true_or_false":
if(!is_string($question->answer)) {
$answer->is_correct = 'Error occured';
continue 2;
}

if(!is_string($answer->answer))
throw new \Exception('The answer '.$answer->number.' has object as answer.');

if(trim($answer->answer) == trim($question->answer)) {
$answer->point = $question->point; 
$answer->is_correct = 'yes';
} else {

if($question->mpoint > 0)
$answer->point = -$question->mpoint;
}

break;
case "multi_choice":

$array1 = $answer->answer;
$array2 = $question->answer;
sort($array1);
sort($array2);


if($array1 == $array2) {
$answer->point = $question->point; 
$answer->is_correct = 'yes';
} else {
if($question->mpoint > 0)
$answer->point = -$question->mpoint;
}

break;
case "fill_the_blanks":
if(in_array($answer->answer, $question->answer)) { 
$answer->point = $question->point; 
$answer->is_correct = 'yes';
} else {
if($question->mpoint > 0)
$answer->point = -$question->mpoint;
}

break;
case "descriptive":

$answer->is_correct = 'review';
$status = 'incomplete';

if(isset($answer->answer->is_evaluated) && $answer->answer->is_evaluated == 'yes') {
if($answer->point > 0) {

$answer->is_correct = 'yes';

} else {
$answer->is_correct = 'no';
}
} else if(is_string($answer->answer)) {
$answer->answer = [
'is_evaluated' => 'no'];

}

break;
case "match_it":

$questions = array_column($answer->answer, 'question');
$answer->point = 0;
foreach($question->answer as $rAnswer) {
$index = array_search($rAnswer->question, $questions);
if($index === false)
continue;

$gAnswer = $answer->answer[$index];
if($gAnswer->question == $rAnswer->question && $gAnswer->answer == $rAnswer->answer) {
$answer->point = $question->point;
$answer->is_correct = 'yes';
} else {
$answer->is_correct = 'no';
if($question->mpoint > 0)
$answer->point = -$question->mpoint;
break;
}
}
break;
}

if($assign->mpoint > 0) {
if($answer->is_correct == 'no')
$answer->point = -(($question->point/100)*$assign->mpoint);
}

$entity = new \App\Entities\Answers($answer->toArray());
$this->answers->update($answer->id, $entity);
} // end of main foreach
return $status;
}

public function setResult($result_id, $status = 'completed', $time = false) {
$attempt = $this->attempts->where('id', $result_id)->first();

$user = $this->users->select('concat(first_name, " ", last_name) as name, "Registered User" as type ')->where('id', $attempt->member_id)->first() ?? $this->anonymoususers->select('name, id, "Anonymous User" as type')->where('id', $attempt->member_id)->first();
$assign = $this->attempts->join('settings', 'settings.id = attempts.assign_id')->select('settings.*')->where('attempts.id', $attempt->id)->first();
$exam = $this->exams->where('id', $assign->exam_id)->first();
$answers = $this->answers->join('questions', 'questions.id = answers.question_id')->select('questions.answer as correct_answer, answers.* ')->where('result_id', $attempt->id)->findAll();
$attend = $this->attempts->join('attend', 'attend.result_id= attempts.id')->select('attend.*')->where('attempts.id', $result_id)->first();
$stats = [
'total_questions' => count($answers), 
'attempted_questions' => 0,
'answered_questions' => 0,
'correct_answers' => 0,
'taken_points' => 0,
'has_passed' => 'no'];
foreach($answers as $ans) {
$stats['attempted_questions'] += $ans->has_attempted == 'yes' ? 1: 0;
$stats['answered_questions'] += $ans->has_answered == 'yes' ? 1: 0;
$stats['correct_answers'] += $ans->is_correct == 'yes' ? 1: 0;
$stats['taken_points'] += $ans->point;
}
if($stats['taken_points'] != 0)
$stats['has_passed'] =  (($stats['taken_points']/$attempt->total_points)*100) >= $assign->pass_mark ? 'yes': 'no';
$result = [
'id' => $attempt->id,
'name' => $user->name,
'member_id' => $attempt->member_id,
'member_type' => $user->type,
'started_on' => $attempt->created_at,
'completed_on' => $attend->last_active,
'status' => $status,
'attempt' =>$attempt->attempt,
'total_points' => $attempt->total_points,

'total_time' => $assign->time_limit,
'taken_time' => $time != false ? $time: $attend->time,
'answers' => $answers,
'setting' => $assign,
'exam' => $exam
];

$result = array_merge($result, $stats);
$entity = new \App\Entities\Results($result);

$this->results->save($entity);

return true;
}



}
