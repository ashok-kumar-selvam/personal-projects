<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;

class Members extends ResourceController
{
protected $modelName = '\App\Models\Users';
protected $format = 'json';
public function __construct() {
helper('Notifications');
$this->user_members = new \App\Models\Admin\UserMembers;
$this->group_members = new \App\Models\Admin\GroupMembers;
$this->groups = new \App\Models\Admin\Groups;
$this->email =  new \App\Controllers\Emails;
$this->users = new \App\Models\Users;
$this->us = new \App\Securities\UserSecurity;
$this->notifications = new \App\Models\Notifications;
$this->is = new \App\Securities\InstructorSecurity;
}


    public function index()
    {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('members', 'view'))
return $this->fail(['message' => $this->is->getError()]);
$members = $this->model->join('user_members', 'user_members.member_id = users.id')->select('users.first_name, users.last_name, users.email, user_members.status, user_members.member_id, user_members.created_at')->where('user_members.user_id', $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid)->orderBy('user_members.created_at', 'desc')->findAll();
$approved = 0;
foreach($members as $member) {
if($member->status == 'approved')
$approved++;
}
$pending = count($members)-$approved;
$stats = [
'total' => count($members),
'approved' => $approved,
'pending' => $pending];

return $this->respond([
'members' => $members,
'stats' => $stats]);
    }

    public function show($id = null)
    {

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('members', 'view'))
return $this->fail(['message' => $this->is->getError()]);$member = $this->model->select('users.first_name, users.last_name, users.email')->where('id', $id)->first();
$user_id = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;
if(!$member)
return $this->failNotFound('Unable to find the member');

$groupsIn = $this->groups->join('group_members', 'group_members.group_id = groups.id')->select('groups.*, group_members.id as join_id')->where('group_members.member_id', $id)->where('groups.user_id', $user_id)->findAll();
$ides = array_column($groupsIn, 'id');
if(!isset($ides[0]))
$ides[0] = 'empty';
$groupsNotIn = $this->groups->whereNotIn('id', $ides)->where('groups.user_id', $user_id)->findAll();

$stats = [
'score' => 1,
'total_exams' => 1,
'total_questions' => 1,
'total_time' => 1];
return $this->respond([
'member' => $member,
'groupsIn' => $groupsIn,
'groupsNotIn' => $groupsNotIn,
'stats' => $stats]);
    }

    public function create()
    {

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('members', 'create'))
return $this->fail(['message' => $this->is->getError()]);
$rules = [
'first_name' => 'required|string',
'last_name' => 'required|string',
'email' => 'required|is_unique[users.email]',
'mobile' => 'if_exist|numeric|is_unique[users.mobile]',
];
if(!$this->validate($rules))
return $this->failValidationErrors($this->validator->getErrors());


$request = (array) $this->request->getVar();
$user = $this->users->where('id', $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid)->first();
if($this->add($user, $request))
return $this->respond(['message' => "The user with email id ".$request['email']." was successfully added and the password has been sent to the provided email. "]);

return $this->fail(['message' => 'Unexpected error occured. Please try again later. ']);

    }

    public function update($id = null)
    {
if(!$this->us->is_owner($id, 'members'))
return $this->fail(['message' => 'You are not allowed to modify this member']);

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('members', 'edit'))
return $this->fail(['message' => $this->is->getError()]);

$action = $this->request->getVar('action');
if(!$action)
return $this->fail(['message' => 'Please provide an action to perform']);
$user_id = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;
switch($action) {
case "approved":
case "suspended":
$this->user_members->where('member_id', $id)->where('user_id', $user_id)->set('status', $action)->update();

break;
default:
return $this->fail(['message' => 'This action is not allowed.']);

}

$member = $this->users->where('id', $id)->first();
$user = $this->users->where('id', $user_id)->first();
$this->email->send([
'name' => $member->first_name,
'email' => $member->email,
'subject' => 'Request accepted in '.getEnv('APP_NAME'),
'message' => " Your request to join as a member with ".$user->first_name." was accepted. Please go to the dashboard to view all exams and quizzes assigned by ".$user->first_name,
'action_url' => getEnv('APP_URL').'/dashboard',
'action_text' => 'Go to dashboard',
]);

notify($member->id, " Your request to join with $user->first_name $user->last_name has been updated. The status is $action");


return $this->respond([
'id' => $id]);

    }

    public function delete($id = null)
    {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('members', 'delete'))
return $this->fail(['message' => $this->is->getError()]);
$user_id = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;
$user = $this->users->where('id', $user_id)->first();
notify($id, " You have been removed from $user->first_name $user->last_name's subscription. ");

$this->user_members->where('user_id', $user_id)->where('member_id', $id)->delete();
$group_members = new \App\Models\Admin\GroupMembers;
$groups = new \App\Models\Admin\Groups;
$group_ids = $groups->where('user_id', $user_id)->findColumn('id') ?? ['abcd'];

$group_members->where('member_id', $id)->whereIn('group_id', $group_ids)->delete();
$assignments = new \App\Models\Settings;
$assign_ids = $assignments->join('exams', 'exams.id = settings.exam_id')->where('exams.user_id', $user_id)->where('settings.assignee_id', $id)->findColumn('settings.id') ?? ['abcd'];
$assignments->whereIn('id', $assign_ids)->delete();


return $this->respondDeleted('successfully deleted');

    }

public function getInvite() {

$isInstructor = $this->is->is_instructor();
$user = $this->users->where('id',                            $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid)->first();

$keys = new \App\Models\Keys;
$origin = $this->request->getHeaderLine('origin');
$url = $keys->where('from', $user->id)->first();
if(!$url) {

$key = $keys->getKey(1000000000, 10000000000);
$from = $user->id;
$keys->insert([
'from' => $from,
'key' => $key]);
} else {
$key = $url->key;
$from = $url->from;
}

$urlText = "$origin/join/$key";

return $this->respond([
'name' => $user->first_name.' '.$user->last_name,
'code' => $key,
'url' => $urlText]);


}

public function upload() {

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('members', 'create'))
return $this->fail(['message' => $this->is->getError()]);
$rules = [
'file' => 'uploaded[file]|ext_in[file,xlsx,xls]',
];
if(!$this->validate($rules))
return $this->failValidationErrors($this->validator->getErrors());
$user = $this->model->where('id', $isInstructor  ? $this->request->user->admin_id: $this->request->user->uuid)->first();

$file = $this->request->getFile('file');
if(!$file->isValid()) 
return $this->fail(['message' => 'Invalid file']);


        $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadSheet = $Reader->load($file);
        $excelSheet = $spreadSheet->getActiveSheet();
$members = $excelSheet->toArray();
$rr = count($members) > 0 ? array_shift($members): false;

$message = '';
foreach($members as $member) {
if(!isset($member[0]) || empty($member[0]) || !isset($member[1]) || empty($member[1]) || !isset($member[2]) || empty($member[2]))
continue;
[$first_name, $last_name, $email] = $member;

if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
$message .= " the email id $email is not valid. \n ";
continue;

}



if($this->model->where('email', $email)->first()) {
$message .= " The member with the email id $email is already exists. Please share the invite link to the member to join. \n ";
continue;
}

if(isset($member[3]) && !empty($member[3])) {
if($this->model->where('mobile', $member[3])->first()) {
$message .= " The mobile number ".$member[3]." is already registered. Please use some other mobile number or leave it as empty. \n ";
continue;

} 

}
$mobile = isset($member[3]) ? $member[3]: '';

$this->add($user, [
'first_name' => $first_name,
'last_name' => $last_name,
'email' => $email,
'mobile' => $mobile,]);
$message .= " The member with $email was added successfully. \n ";


}
return $this->respond([
'message' => $message]);


}

public function add($user, $data) {
helper('text');
// step 1
$data['password'] = random_string('alnum', 8);
$data['status'] = 'pending';
$data['type'] = 'member';

// step 2
$entity = new \App\Entities\Users($data);
$id = $this->model->insert($entity);
// step 3
$keys = new \App\Models\Keys;
$key = $keys->getKey();
$keys->insert([
'key' => $key,
'to' => $id,
'expire' => time()+(86400*7),
]);

// step 4
$this->user_members->insert([
'user_id' => $user->id,
'member_id' => $id,
'status' => 'approved',
]);

// step 5
$result = $this->email->sendinblue([
'email' => $data['email'],
'subject' => "$user->first_name has invited you to ".APP_NAME,
'message' => '',
'user_id' => $id,
'admin' => "$user->first_name $user->last_name",
'otp' => $key,
'username' => $data['email'],
'password' => $data['password'],
'file' => 'welcome_member.php'], true, 1);
return $result;

}


}
