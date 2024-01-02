<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;

class Assignments extends ResourceController
{

protected $modelName = '\App\Models\Settings';
protected $format = 'json';

public function __construct() {
$this->users = new \App\Models\Users;
$this->groups = new \App\Models\Admin\Groups;
$this->anonymoususers = new \App\Models\AnonymousUsers;
$this->us = new \App\Securities\UserSecurity;
$this->exams = new \App\Models\Exams;
$this->email = new \App\Controllers\Emails;
$this->is = new \App\Securities\InstructorSecurity;
$this->db = db_connect();
$this->credits = new \App\Subscriptions\Credits;
}


    public function index()
    {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('assignments', 'view'))
return $this->fail(['message' => $this->is->getError()]);

$request = $this->request->getVar();
$user_id = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;
$where = "exams.user_id = \"$user_id\"";
$entity = null;
if(isset($request['type']) && isset($request['id'])) {
$type = $request['type'];
$id = $request['id'];

switch($request['type']) {
case "groups":
if(!$this->us->is_owner($id, 'groups'))
return $this->fail(['message' => 'You are not authorised to access this resource. ']);
$entity = $this->db->table('groups')->select('name as title')->where('id', $id)->get();

$where = "exams.user_id = \"$user_id\" and settings.assign_to = 'single_group' and settings.assignee_id = \"$id\"";
break;
case "members":
if(!$this->us->is_owner($id, 'members'))
return $this->fail(['message' => 'You are not authorised to access this resource. ']);
$entity = $this->db->table('users')->select('concat(users.first_name, " ", users.last_name) as title')->where('id', $id)->get();

$where = "exams.user_id = \"$user_id\" and settings.assign_to = 'single_member' and settings.assignee_id = \"$id\"";
break;
case "exams":
if(!$this->us->is_owner($id, 'exams'))
return $this->fail(['message' => 'You are not authorised to access this resource. ']);
$entity = $this->db->table('exams')->where('id', $id)->get();
$where = "exams.user_id = \"$user_id\" and exams.id = \"$id\"";

break;
default:
$where = "exams.user_id = \"$user_id\"";

} //end of switch


} //end of if
$assignments = $this->model->join('exams', 'exams.id = settings.exam_id')->select('settings.*, exams.title')->where($where)->orderBy('settings.id', 'desc')->findAll();

foreach($assignments as $assign) {
if($assign->assign_to == 'member') {
$member = $this->users->where('id', $assign->assignee_id)->first();

$assign->name = $member->first_name." ".$member->last_name;
} else if($assign->assign_to == 'group') {
$group = $this->groups->where('id', $assign->assignee_id)->first();
$assign->name = $group->name;
} else if($assign->assign_to == 'all_members') {
$assign->name = 'All Members';
} else if($assign->assign_to == 'anonymous_users') {
$assign->name = 'Anonymous Users';
}


}
$entity = $entity ? $entity->getRow(): null;
return $this->respond([
'title' => $entity ? $entity->title: null,
'assignments' => $assignments]);

    }

    public function show($id = null)
    {
if(!$this->us->is_owner($id, 'assignments'))
return $this->fail(['message' => 'You are not allowed to access this resource. ']);

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('assignments', 'view'))
return $this->fail(['message' => $this->is->getError()]);

$exam = $this->model->join('exams', 'exams.id = settings.exam_id')->select('settings.*, exams.title')->where('settings.id', $id)->where('exams.user_id', $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid)->first();
if(!$exam)
return $this->failNotFound(' Unable to find the assignment');

$users = $this->db->table('attempts')->join('results', 'attempts.id = results.id')->select(' count(distinct attempts.member_id) as users, count(*) as results  ')->where('attempts.assign_id', $id)->get();
$users = $users->getRow();

$options = [
'attempts' => $this->db->table('attempts')->where('assign_id', $id)->countAllResults(),
'results' => $users->results,
'users' => $users->users,
'status' => $exam->end_time == 0 || ($exam->end_time/1000) > time() ? 'Running': (($exam->end_time/1000) < time() ?  'Expired': 'don not know'),
];

return $this->respond([
'settings' => $exam,
'options' => $options,
]);
    }

    public function assignees()
    {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('assignments', 'create') && !$this->is->has_access('assignments', 'edit'))
return $this->fail(['message' => $this->is->getError()]);

$assign_to = $this->request->getVar('assign_to');
if(!$assign_to)
return $this->fail(['message' => 'Invalid request']);
switch($assign_to) {
case 'single_member':
$assignees = $this->users->join('user_members', 'users.id = user_members.member_id')->select('users.id, concat(users.first_name, " ", users.last_name) as name')->where('user_members.user_id', $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid)->where('user_members.status', 'approved')->orderBy('users.first_name', 'asc')->findAll();
break;
case 'single_group':
$assignees = $this->groups->select('id, name')->where('user_id', $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid)->findAll();
break;
default:
$assignees = [];
}

return $this->respond($assignees);

    }

    public function create()
    {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('assignments', 'create'))
return $this->fail(['message' => $this->is->getError()]);
helper('Credit');
$remaining_credits = $this->credits->getAvailable($isInstructor ? $this->request->user->admin_id: $this->request->user->uuid);
if($remaining_credits <= 0)
return $this->fail(['message' => 'There is no remaining credits. Please buy more credits to proceed']);


$rules = [
'exam_id' => 'required|is_owner[exam_id,exams]|is_not_unique[exams.id]|has_questions[exam_id]',
'assign_to' => 'required|in_list[single_member,single_group,all_members,anonymous_users]',
'assignee_id' => 'required_if[assign_to,single_member,single_group]|is_assignee[assignee_id]',
'allowed_access' => 'required_if[assign_to,anonymous_users]',
'resumable' => 'if_exist|in_list[yes,no]',
'allowed_emails' => 'permit_empty|if_exist|valid_emails',
'start_time' => 'required|valid_date',
'end_time' => 'required',
'allowed_attempts' => 'if_exist|integer',
'question_random' => 'if_exist|in_list[yes,no]',
'option_random' => 'if_exist|in_list[yes,no]',
'pass_mark' => 'if_exist|numeric',
'mpoint' => 'if_exist|numeric',
'time_limit' => 'if_exist|numeric',
'time_decrement' => 'if_exist|numeric',
'question_time' => 'if_exist|numeric',
'result_type' => 'required|in_list[pass_or_fail,simple_result,complete_result]',
'result_method' => 'if_exist|in_list[immediate,automatic,later]',
'publish_on' => 'if_exist|valid_date',
'show_explanation' => 'if_exist|in_list[yes,no]',
'message' => 'if_exist|string',
'is_active' => 'if_exist|in_list[yes,no]'];

$errors = [
'assignee_id' => [
'required_if' => 'Please select the group or member '
],
'allowed_access' => [
'required_if' => 'Please provide how many users are allowed to access the anonymous llink'],
];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());


$request = (array) $this->request->getVar();
if(!$this->us->check_access_assignments($request))
return $this->fail(['message' => $this->us->getError()]);

//$request['published'] = ($request['result_method'] == 'immediate' || $request['result_method'] == 'automatic') ? 'yes': 'no';
$request['published'] = 'yes';
$user_id = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;
$request['assignee_id'] = ($request['assign_to'] == 'all_members') ? $user_id: $request['assignee_id'];

$entity = new \App\Entities\Settings($request);

$id = $this->model->insert($entity);
if(isset($request['is_active']) && $request['is_active'] == 'yes') {

$assign = $this->exams->join('users', 'users.id = exams.user_id')->select('concat(users.first_name, " ", users.last_name) as name, exams.title')->where('exams.id', $request['exam_id'])->first();

$this->notify([
'assign_id' => $id,
'subject' => 'An exam assigned in '.getEnv('APP_NAME'),
'message' => " You have been assigned an exam ( ".$assign->title." ) by ".$assign->name.". The exam starts on ".date(' F m Y H:i:s', strtotime($request['start_time']))." and ends on ".date('F m Y H:i:s', strtotime($request['end_time']))." Please click below to access the exam. Wish you the best! ",
'action_url' => getEnv('APP_URL').'/exams/'.$id,
'action_text' => 'Click here to start']);

}


return $this->respond([
'id' => $id]);

    }



    public function update($id = null)
    {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('assignments', 'edit'))
return $this->fail(['message' => $this->is->getError()]);

$rules = [
'id' => 'required|is_owner[id,assignments]|is_not_unique[settings.id]',
'segment' => 'required|in_list[instructions,users,questions,time,results]'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();

$exam = $this->model->join('exams', 'exams.id = settings.exam_id')->select('settings.*, exams.title')->where('settings.id', $request['id'])->where('exams.user_id', $this->request->user->uuid)->first();

switch($request['segment']) {
case "instructions":
$rules = [
'instructions' => 'required',
];
$errors = [];

break;
case "users":
$rules = [
'assign_to' => 'if_exist|in_list[single_member,single_group,all_members,anonymous_users]',
'assignee_id' => 'required_if[assign_to,single_member,single_group]|is_assignee[assignee_id]',
'allowed_access' => 'required_if[assign_to,anonymous_users]',
'resumable' => 'if_exist|in_list[yes,no]',
'allowed_emails' => 'permit_empty|if_exist|valid_emails'];

$errors = [
'assignee_id' => [
'required_if' => 'Please select the group or member '
],
'allowed_access' => [
'required_if' => 'Please provide how many users are allowed to access the anonymous llink'],
];

break;
case "questions":
$rules = [
'allowed_attempts' => 'if_exist|integer',
'question_random' => 'if_exist|in_list[yes,no]',
'option_random' => 'if_exist|in_list[yes,no]',
'pass_mark' => 'if_exist|numeric',
'mpoint' => 'if_exist|numeric'];

$errors = [];

break;
case "time":
$rules = [
'start_time' => 'if_exist|valid_date',
'time_limit' => 'if_exist|numeric',
'time_decrement' => 'if_exist|numeric',
'question_time' => 'if_exist|numeric'];
$errors = [];

break;
case "results":
$rules = [
'result_type' => 'if_exist|in_list[pass_or_fail,simple_result,complete_result]',
'result_method' => 'if_exist|in_list[immediate,automatic,later]',
'publish_on' => 'if_exist|valid_date',
'published' => 'if_exist|in_list[yes,no]',
'show_explanation' => 'if_exist|in_list[yes,no]',
'message' => 'if_exist|string',
'is_active' => 'if_exist|in_list[yes,no]'];
$errors = [];

break;
}

if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();
if(!$this->us->check_access_assignments($request))
return $this->fail(['message' => $this->us->getError()]);

if(isset($request['exam_id']))
unset($request['exam_id']);

/*
if(isset($request['assign_to']))
unset($request['assign_to']);

if(isset($request['assignee_id']))
unset($request['assignee_id']);
*/

if(isset($request['assign_to']) && $request['assign_to'] == 'all_members')
$request['assignee_id'] = $this->request->user->uuid;

$entity = new \App\Entities\Settings($request);
$this->model->update($id, $entity);
return $this->respond([
'id' => $id]);


    }

    public function delete($id = null)
    {
if(!$this->us->is_owner($id, 'assignments'))
return $this->fail(['message' => 'You cannot delete this resource. ']);

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('assignments', 'delete'))
return $this->fail(['message' => $this->is->getError()]);
$this->model->where('id', $id)->delete();
return $this->respondDeleted(' deleted successfully ');
    }

public function publish() {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('assignments', 'edit'))
return $this->fail(['message' => $this->is->getError()]);

$rules = [
'id' => 'required|is_owner[id,assignments]|is_not_unique[settings.id]',
'status' => 'required|in_list[yes,no]'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();
$this->model->update($request['id'], ['published' => $request['status']]);
return $this->respond([
'message' => 'success']);

}

public function activate() {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('assignments', 'edit'))
return $this->fail(['message' => $this->is->getError()]);

$rules = [
'id' => 'required|is_owner[id,assignments]|is_not_unique[settings.id]',
'status' => 'required|in_list[yes,no]'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();
$this->model->update($request['id'], ['is_active' => $request['status']]);
/*
$assign = $this->model->join('exams', 'exams.id = settings.exam_id')->join('users', 'users.id = exams.user_id')->select('concat(users.first_name, " ", users.last_name) as name, exams.title, exams.user_id, settings.* ')->where('settings.id', $request['id'])->first();

if($request['status'] == 'yes') {
$this->notify([
'assign_id' => $assign->id,
'subject' => 'An exam assigned in '.getEnv('APP_NAME'),
'message' => " You have been assigned an exam ( ".$assign->title." ) by ".$assign->name.". The exam starts on ".date(' F m Y H:i:s', $assign->start_time/1000)." and ends on ".date('F m Y H:i:s', $assign->end_time/1000)." Please click below to access the exam. Wish you the best! ",
'action_url' => getEnv('APP_URL').'/exams/'.$assign->id,
'action_text' => 'Click here to start']);
} else {

$this->notify([
'assign_id' => $assign->id,
'subject' => 'The exam canceled',
'message' => " The exam ( ".$assign->title." )  assigned by ".$assign->name.". was canceled. Please contact your admin for more information.",
'action_url' => getEnv('APP_URL').'/exams/'.$assign->id,
'action_text' => 'Click here to start']);

}
*/
return $this->respond(['message' => 'updated']);

}

public function results($assign_id) {

if(!$this->us->is_owner($assign_id, 'assignments'))
return $this->fail(['message' => 'You are not allowed to access this resource']);
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('results', 'view'))
return $this->fail(['message' => $this->is->getError()]);
$rules = [
'mode' => 'required|in_list[anonymous,member]',
'search' => 'if_exist|string'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();
$user_id = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;
$assignment = $this->model->join('exams', 'exams.id = settings.exam_id')->select('settings.start_time, settings.end_time, exams.title, settings.assign_to, settings.assignee_id, settings.allowed_emails ')->where('settings.id', $assign_id)->first();
switch($request['mode']) {
case "member":
$results = $this->model->join('attempts', 'attempts.assign_id = settings.id')->join('attend', 'attend.result_id = attempts.id')->join('users', 'users.id = attempts.member_id')->join('user_members', 'user_members.member_id = users.id')->select('attend.last_active as completed_on, attend.time as taken_time, attempts.attempt, attempts.member_id, attempts.status, attempts.id as result_id, concat(users.first_name, " ", users.last_name) as name  ')->where('settings.id', $assign_id)->where('user_members.user_id', $user_id)->orderBy('attend.last_active', 'desc')->findAll();
break;
case "anonymous":
$results = $this->model
->join('attempts', 'attempts.assign_id = settings.id')
->join('attend', 'attend.result_id = attempts.id')
->join('anonymous_users', 'anonymous_users.id = attempts.member_id', 'left')
->join('users', 'users.id = attempts.member_id', 'left')
->select('attend.last_active as completed_on, attend.time as taken_time, attempts.attempt, attempts.member_id, attempts.status, attempts.id as result_id, ifnull(anonymous_users.email, users.email) as email, ifnull(anonymous_users.name, concat(users.first_name, " ", users.last_name)) as name ')
->where(" settings.id = \"$assign_id\" and attempts.member_id not in (select member_id from user_members where user_id =  \"$user_id\" and status = 'approved')")
->orderBy('attend.last_active', 'desc')->findAll();
break;
}

return $this->respond([
'assignment' => $assignment,
'results' => $results]);

}

public function notify($data) {
if(!isset($data['subject']) || !isset($data['message']) || !isset($data['action_url']) || !isset($data['action_text']) || !isset($data['assign_id']))
throw new \Exception(' Required params are not given for assign notification. ');

$assignment = $this->model->join('exams', 'exams.id = settings.exam_id')->join('users', 'users.id = exams.user_id')->select('exams.title, exams.user_id, concat(users.first_name, " ", users.last_name) as name, settings.*')->where('settings.id', $data['assign_id'])->first();
if(!$assignment)
throw new \Exception(' Assignment is not found for notification. Please inform it to the admin. ');



switch($assignment->assign_to) {
case "single_member":
$user = $this->users->where('id', $assignment->assignee_id)->first();
$this->email->send(array_merge($data, [
'name' => $user->first_name,
'email' => $user->email,
]));
break;
case "single_group":
$users = $this->users->join('group_members', 'group_members.member_id = users.id')->select('users.*')->where('group_members.group_id', $assignment->assignee_id)->findAll();
foreach($users as $user) {
$this->email->send(array_merge($data, [
'name' => $user->first_name,
'email' => $user->email,
]));
}

break;
case "all_members":
$users = $this->users->join('user_members', 'users.id = user_members.member_id')->select('users.*')->where('user_members.user_id', $assignment->user_id)->where('status', 'approved')->findAll();

foreach($users as $user) {
$this->email->send(array_merge($data, [
'name' => $user->first_name,
'email' => $user->email,
]));
}
break;
case "anonymous_users":
if(!is_null($assignment->allowed_emails) && count($assignment->allowed_emails) > 0) {
foreach($assignment->allowed_emails as $email) {
$this->email->send(array_merge($data, [
'name' => 'User',
'email' => $email,
]));
}
}
break;
}
return true;
}

}
