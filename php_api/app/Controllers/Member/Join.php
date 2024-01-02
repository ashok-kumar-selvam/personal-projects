<?php

namespace App\Controllers\Member;

use CodeIgniter\RESTful\ResourceController;

class Join extends ResourceController
{

public function __construct() {
helper('Notifications');
$this->keys = new \App\Models\Keys;
$this->user_members = new \App\Models\Admin\UserMembers;
$this->users = new \App\Models\Users;
$this->email = new \App\Controllers\Emails;
$this->notifications = new \App\Models\Notifications;
}


    public function show($id = null)
    {
$user = $this->keys->join('users', 'users.id = keys.from')->select('users.first_name, users.last_name, users.id')->where('key', $id)->first();
if(!$user)
return $this->failNotFound('Unable to find the teacher');

$exists = $this->user_members->where('user_id', $user->id)->where('member_id', $this->request->user->uuid)->first();
if($exists) {
$exists = (object) $exists;

if($exists->status == 'approved') {
return $this->fail('You are already a member of this teacher');
} else if($exists->status == 'pending') {
return $this->fail('You have already sent request to the same teacher before. Please wait untill the teacher approves your request.');
} else if($exists->status == 'rejected') {
return $this->fail('The teacher has already rejected your request before. If the teacher is willing to accept you now, Please ask the teacher to contact the admin.');
} else {
return $this->fail($exists->status);
}

}

return $this->respond($user);
    }

    public function create()
    {
$rules = [
'teacher_id' => 'required|is_not_unique[keys.key]'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$id = $this->request->getVar('teacher_id');



$user = $this->keys->join('users', 'users.id = keys.from')->select('users.first_name, users.last_name, users.id, users.email ')->where('keys.key', $id)->first();
if(!$user)
return $this->failNotFound('Unable to find the teacher');



$id = $this->user_members->insert([
'user_id' => $user->id,
'member_id' => $this->request->user->uuid]);

$member = $this->users->where('id', $this->request->user->uuid)->first();
$email = new \App\Controllers\Emails;
$email->send([
'name' => $user->first_name,
'email' => $user->email,
'subject' => 'New member request from '.getEnv('APP_NAME'),
'message' => $member->first_name." has sent a request to join as a member with you. To approve and see all member requests, Please visit the all members page",
'action_url' => getEnv('APP_URL').'/admin/members',
'action_text' => 'All members',
]);


notify($member->id, " You have sent a request to join with $user->first_name $user->last_name");
notify($user->id, "$member->first_name $member->last_name has requested to join with you.");

return $this->respond([
'id' => $id]);

    }

    public function delete($id = null)
    {
$group_members = new \App\Models\Admin\GroupMembers;
$group_ids = $group_members->join('groups', 'groups.id = group_members.group_id')->where('groups.user_id', $id)->where('group_members.member_id', $this->request->user->uuid)->findColumn('group_members.id') ?? ['abcd'];
$group_members->whereIn('id', $group_ids)->delete();
$assignments = new \App\Models\Settings;
$assign_ids = $assignments->join('exams', 'exams.id = settings.exam_id')->where('exams.user_id', $id)->where('settings.assignee_id', $this->request->user->uuid)->findColumn('settings.id') ?? ['abcd'];
$assignments->whereIn('id', $assign_ids)->delete();


$this->user_members->where('user_id', $id)->where('member_id', $this->request->user->uuid)->delete();
$user = $this->users->where('id', $this->request->user->uuid)->first();
notify($id, "$user->first_name $user->last_name has unsubscribed from you. ");

return $this->respondDeleted('successfully deleted');

    }
}
