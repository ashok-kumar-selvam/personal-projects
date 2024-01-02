<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;

class Exams extends ResourceController
{
protected $modelName = '\App\Models\Exams';
protected $format = 'json';


public function __construct() {
$this->questions = new \App\Models\Questions;
$this->results = new \App\Models\Results;
$this->stats = new \App\Controllers\Admin\Stats;
$this->attempts = new \App\Models\Attempts;
$this->users = new \App\Models\Users;
$this->anonymoususers = new \App\Models\AnonymousUsers;
$this->settings = new \App\Models\Settings;
$this->us = new \App\Securities\UserSecurity;
$this->groups = new \App\Models\Admin\Groups;
$this->qsheet = new \App\Models\QuestionSheet;
$this->is = new \App\Securities\InstructorSecurity;
}

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('exams', 'view'))
return $this->fail(['message' => $this->is->getError()]);

$exams = $this->model->where('user_id', $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid)->orderBy('created_at', 'desc')->findAll();
foreach($exams as $exam) {
$exam->questions = $this->questions->join('question_sheet', 'questions.id = question_sheet.question_id')->where('question_sheet.entity_id', $exam->id)->countAllResults();

}

return $this->respond([
'exams' => $exams]);
    }

    public function show($id = null)
    {  
$exam = $this->model->where('id', $id)->first();
if(!$exam)
return $this->failNotFound('Unable to find the exam');

if(!$this->us->is_owner($id, 'exams'))
return $this->fail(['message' => 'You are not allowed to access this resource. ']);


$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('exams', 'view'))
return $this->fail(['message' => $this->is->getError()]);

$questions = $this->questions->join('question_sheet', 'questions.id = question_sheet.question_id')->select('questions.*')->where('question_sheet.entity_id', $id)->findAll();
$assignments = $this->settings->select(' id, assign_to, assignee_id, start_time ')->where('exam_id', $exam->id)->orderBy('created_at', 'desc')->findAll(10);


foreach($assignments as $assign) {
if($assign->assign_to == 'member') {
$member = $this->users->where('id', $assign->assignee_id)->first();

$assign->name = $member->first_name." ".$member->last_name;
} else if($assign->assign_to == 'group') {
$group = $this->groups->where('id', $assign->assignee_id)->first();
$assign->name = $group->name;
} else if($assign->assign_to == 'all_members') {
$assign->name = 'All members';
} else if($assign->assign_to == 'anonymous_users') {
$assign->name = 'Anonymous users';
}


}

$details = [
'category' => $exam->subject,
'created_at' => $exam->created_at,
'questions' => count($questions),
'points' => $this->qsheet->getPoints($exam->id),
'results' => $this->attempts->join('settings', 'settings.id = attempts.assign_id')->join('exams', 'exams.id = settings.exam_id')->where('settings.exam_id', $exam->id)->countAllResults(),
'assignments' => $this->settings->where('exam_id', $exam->id)->countAllResults(),

];


return $this->respond([
'exam' => $exam,
'questions' => $questions,
'assignments' => $assignments,
'details' => $details]);


  }


    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {

$rules = [
'title' => 'required',
'subject' => 'if_exist|string',
'description' => 'required'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('exams', 'create'))
return $this->fail(['message' => $this->is->getError()]);
$request = (array) $this->request->getVar();
$request['user_id'] = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;
if(!isset($request['subject']))
$request['subject'] = 'general';

$id = $this->model->insert($request);
return $this->respondCreated([
'id' => $id]);


    }


    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
if(!$this->us->is_owner($id, 'exams'))
return $this->fail(['message' => 'You cannot modify this resource. ']);

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('exams', 'edit'))
return $this->fail(['message' => $this->is->getError()]);

$request = (array) $this->request->getVar();
if(isset($request['user_id']))
unset($request['user_id']);

$entity = new \App\Entities\Exams($request);

$this->model->update($id, $entity);
return $this->respond([
'id' => $id]);

    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
if(!$this->us->is_owner($id, 'exams'))
return $this->fail(['message' => 'You are not allowed to delete this resource. ']);

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('exams', 'delete'))
return $this->fail(['message' => $this->is->getError()]);

$result_ids = $this->attempts->join('settings', 'settings.id = attempts.assign_id')
->join('results', 'results.id = attempts.id')
->select('results.id')
->where('settings.exam_id', $id)
->findAll();

foreach($result_ids as $result_id) 
$this->results->update($result_id->id, ['deleted' => 'yes']);
$this->qsheet->where('entity_id', $id)->delete();
$this->settings->where('exam_id', $id)->delete();
$this->model->where('id', $id)->delete();
return $this->respondDeleted(' successfully deleted. ');
    }

public function clear_questions($id) {
if(!$this->us->is_owner($id, 'exams'))
return $this->fail(['message' => 'You are not allowed to delete this resource. ']);

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('exams', 'delete'))
return $this->fail(['message' => $this->is->getError()]);
$this->qsheet->where('entity_id', $id)->delete();

return $this->respondDeleted('success');
}


}
