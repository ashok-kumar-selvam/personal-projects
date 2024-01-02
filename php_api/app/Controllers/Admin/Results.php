<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;

class Results extends ResourceController
{
protected $modelName = '\App\Models\Results';
protected $format = 'json';

public function __construct() {

$this->questions = new \App\Models\Questions;
$this->exams = new \App\Models\Exams;
$this->answers = new \App\Models\Answers;
$this->settings = new \App\Models\Settings;
$this->users = new \App\Models\Users;
$this->anonymoususers = new \App\Models\AnonymousUsers;
$this->attempts = new \App\Models\Attempts;
$this->us = new \App\Securities\UserSecurity;
$this->db = db_connect();
$this->is = new \App\Securities\InstructorSecurity;
$this->corrections = new \App\Libraries\Corrections\Corrections;


}


    public function index()
    {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('results', 'view'))
return $this->fail(['message' => $this->is->getError()]);
$rules = [
'assign_id' => 'required|is_not_unique[settings.id]'];

$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$assign_id = $this->request->getVar('assign_id');

$results = $this->attempts->join('exams', 'exams.id = results.quiz_id')->join('users', 'users.id = results.member_id')->select('exams.title, users.first_name, users.last_name, results.*')->where('results.setting_id', $assign_id)->findAll();
return $this->respond($results);

    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('results', 'view'))
return $this->fail(['message' => $this->is->getError()]);
$attempt = $this->attempts->where('id', $id)->first();
if(!$attempt)
return $this->failNotFound(' Unable to find the result. ');

if($attempt->status == 'completed') {
$result = $this->model->where('id', $id)->first();
$result->answers = $this->answers->join('questions', 'questions.id = answers.question_id')->select('questions.answer as correct_answer, answers.* ')->where('result_id', $result->id)->findAll();
return $this->respond($result);
}
// variables for result building

$assign = $this->attempts->join('settings', 'settings.id = attempts.assign_id')->select('settings.*')->where('attempts.id', $id)->first();
$exam = $this->exams->where('id', $assign->exam_id)->first();
$user = $this->users->select('concat(first_name, " ", last_name) as name ')->where('id', $attempt->member_id)->first() ?? $this->anonymoususers->where('id', $attempt->member_id)->first();
$answers = $this->answers->join('questions', 'questions.id = answers.question_id')->select('questions.answer as correct_answer, answers.* ')->where('result_id', $id)->findAll();
$stats = ['attempted_questions' => 0, 'answered_questions' => 0, 'correct_answers' => 0, 'taken_points' => 0, 'total_questions' => count($answers)];
foreach($answers as $ans) {
$stats['attempted_questions'] += $ans->has_attempted == 'yes' ? 1: 0;
$stats['answered_questions'] += $ans->has_answered == 'yes' ? 1: 0;
$stats['correct_answers'] += $ans->is_correct == 'yes' ? 1: 0;
$stats['taken_points'] += $ans->point;

}
$attend = $this->attempts->join('attend', 'attend.result_id = attempts.id')->select('attend.*')->where('attempts.id', $id)->first();

$result = [
'id' => $attempt->id,
'name' => $user->name,
'member_type' => $this->us->is_anonymous_user() ? 'anonymous': 'registered',
'started_on' => strtotime($attempt->created_at)*1000,
'completed_on' => strtotime($attend->last_active)*1000,
'status' => $attempt->status,
'attempt' => $attempt->attempt,
'total_points' => $attempt->total_points,
'total_time' => $assign->time_limit,
'taken_time' => $attend->time/60,
'answers' => $answers,
'setting' => $assign,
'exam' => $exam];
$result = array_merge($result, $stats);


if(!$result)
return $this->fail(['message' => 'Unexpected error, The student did not complete the exam properly.']); 

return $this->respond($result);


    }

    public function delete($id = null)
    {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('results', 'delete'))
return $this->fail(['message' => $this->is->getError()]);
$attempt = $this->attempts->where('id', $id)->first();
if(!$attempt)
return $this->failNotFound('Unable to find the result. ');

$assign = $this->settings->join('exams', 'exams.id = settings.exam_id')->select('exams.user_id')->where('settings.id', $attempt->assign_id)->first();
if($assign->user_id != ($isInstructor ? $this->request->user->admin_id: $this->request->user->uuid))
return $this->fail(['message' => " You don't have permission to do it "]);
$this->db->table('attempts')->where('id', $id)->delete();
$this->db->table('attend')->where('result_id', $id)->delete();
$this->db->table('answers')->where('result_id', $id)->delete();
$this->db->table('results')->where('id', $id)->delete();
return $this->respondDeleted('successfully deleted');
    }

public function reevaluate($result_id) {

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('results', 'edit'))
return $this->fail(['message' => $this->is->getError()]);
$result = $this->model->where('id', $result_id)->first();
if(!$result)
return $this->failNotFound('Unable to find the result. Try finishing the exam then make reevaluation. ');

$exams = new \App\Controllers\Common\Exams;
$status = $this->corrections->run($result_id);

if(!$status)
return $this->fail(['message' => $this->corrections->getError()]);

$saved = $exams->setResult($result_id);
return $this->respond([
'result' => $this->model->where('id', $result_id)->first()]);


}

public function point() {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('results', 'edit'))
return $this->fail(['message' => $this->is->getError()]);$result = $this->model->where('id', $result_id)->first();
$rules = [
'answer_id' => 'required|is_not_unique[answers.id]',
'point' => 'required|numeric'];
if(!$this->validate($rules))
return $this->failVallidationErrors($this->validator->getErrors());

$request = (array) $this->request->getVar();
$answer = $this->answers->where('id', $request['answer_id'])->first()->toArray();


if($answer['type'] == 'descriptive') {
$answer['answer']->is_evaluated = 'yes';
$answer['point'] = $request['point'];
$answer['is_correct'] = ($answer['point'] > 0) ? 'yes': 'no';
$entity = new \App\Entities\Answers($answer);
$this->answers->update($answer['id'], $entity);
return $this->respond('success');

}

$this->answers->where('id', $request['answer_id'])->set('point', $request['point'])->update();
return $this->respond('success');
}

public function additional() {

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('results', 'edit'))
return $this->fail(['message' => $this->is->getError()]);$result = $this->model->where('id', $result_id)->first();
$rules = [
'result_id' => 'required|is_not_unique[results.id]',
'attempt' => 'if_exist|numeric',
'time' => 'if_exist|numeric'];
if(!$this->validate($rules))
return $this->failValidationErrors($this->validator->getErrors());

$request = (array) $this->request->getVar();
if(isset($request['time']) && $request['time'] > 0)
$request['time'] = $request['time']*60;


$attempt = $this->attempts->where('id', $request['result_id'])->first();
$additionals = new \App\Models\Additional;

$additionals->insert(array_merge([
'assign_id' => $attempt->assign_id,
'member_id' => $attempt->member_id,
], $request));

return $this->respond('success');

}


public function add_review() {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('results', 'edit'))
return $this->fail(['message' => $this->is->getError()]);$result = $this->model->where('id', $result_id)->first();

$rules = [
'id' => 'required|is_not_unique[results.id]',
'review' => 'required|string'
];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$request = (array) $this->request->getVar();
$owner = $this->settings
->join('attempts', 'attempts.assign_id = settings.id')
->join('exams', 'exams.id = settings.exam_id')
->select('exams.user_id')
->where('attempts.id', $request['id'])
->first();

if(!$owner)
return $this->failNotFound(' unable to find the owner');
if($owner->user_id != $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid)
return $this->fail(['message' => 'You are not allowed to edit this result.']);

$this->model->update($request['id'], ['review' => $request['review']]);
return $this->respond([
'status' => 'success']);

}

public function setComplete($result_id) {
$attempt = $this->attempts->where('id', $result_id)->first();
if(!$attempt)
return $this->failNotFound('Unable to find the attempt');
$this->attempts->where('id', $result_id)->set('status', 'completed')->update();
$exams = new \App\Controllers\Common\Exams;
$status = $this->corrections->run($result_id);
if(!$status)
return $this->fail(['message' => $this->corrections->getError()]);


$saved = $exams->setResult($result_id, $status);
return $this->respond('success');

}


}
