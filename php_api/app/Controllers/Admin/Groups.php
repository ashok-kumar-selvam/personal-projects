<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;

class Groups extends ResourceController
{

protected $modelName = '\App\Models\Admin\Groups';
protected $format = 'json';

public function __construct() {
$this->groupmembers = new \App\Models\Admin\GroupMembers;
$this->user_members = new \App\Models\Admin\UserMembers;
$this->users = new \App\Models\Users;
$this->us = new \App\Securities\UserSecurity;
$this->is = new \App\Securities\InstructorSecurity;
}


    public function index()
    {

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('groups', 'view'))
return $this->fail(['message' => $this->is->getError()]);

try {
$groups = $this->model->where('user_id', $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid)->findAll();
foreach($groups as $group) {
$group->total = $this->groupmembers->where('group_id', $group->id)->countAllResults();
}


return $this->respond([
'groups' => $groups]);
} catch(Exception $e) {
return $this->fail([
'message' => $e->getMessage()]);
}

    }

    public function show($id= null)
    {

if(!$this->us->is_owner($id, 'groups'))
return $this->fail(['message' => 'You are not allowed to access this resource. ']);
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('groups', 'view'))
return $this->fail(['message' => $this->is->getError()]);
$group = $this->model->where('id', $id)->first();
if(!$group)
return $this->failValidationError('The group is not found');

$members = $this->groupmembers->join('users', 'users.id = group_members.member_id')->select('concat(users.first_name, " ", users.last_name) as name, users.id, users.email  ')->where('group_members.group_id', $id)->findAll();


return $this->respond([
'group' => $group,
'members' => $members]);
    }

    public function create()
    {

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('groups', 'create'))
return $this->fail(['message' => $this->is->getError()]);

$rules = [

'name' => 'required',
'description' => 'required'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
if(!$this->us->check_access_count('groups'))
return $this->fail(['message' => $this->us->getError()]);
$request = (array) $this->request->getVar();
$request['user_id'] = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;
$id = $this->model->insert($request);
return $this->respond([
'id' => $id]);

    }


    public function update($id = null)
    {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('groups', 'edit'))
return $this->fail(['message' => $this->is->getError()]);

$rules = [
'id' => 'required|is_owner[id,groups]|is_not_unique[groups.id]'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();
$this->model->update($id, $request);

return $this->respond([
'id' => $id]);
    }

    public function delete($id = null)
    {
if(!$this->us->is_owner($id, 'groups'))
return $this->fail(['message' => 'You are not allowed to delete this group. ']);

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('groups', 'delete'))
return $this->fail(['message' => $this->is->getError()]);
db_connect()->table('settings')->where('assignee_id', $id)->where('assign_to', 'single_group')->delete();

$this->groupmembers->where('group_id', $id)->delete();
$this->model->delete($id);
return $this->respondDeleted('success');
    }

public function add_members() {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('groups', 'edit'))
return $this->fail(['message' => $this->is->getError()]);

$rules = [
'group_id' => 'required|is_owner[group_id,groups]|is_not_unique[groups.id]',
'ids' => 'required'];


if(!$this->validate($rules))
return $this->failValidationErrors($this->validator->getErrors());

$request = (array) $this->request->getVar();

if(count($request['ids']) <= 0)
return $this->fail(['message' => ' Please select at least one id. ']);

foreach($request['ids'] as $id) {
$insert = [
'group_id' => $request['group_id'],
'member_id' => $id];
if(!$this->groupmembers->where($insert)->first()) 
$this->groupmembers->insert($insert);
}

return $this->respondCreated('success');
}

public function remove_member($group_id, $member_id) {

if(!$this->us->is_owner($group_id, 'groups'))
return $this->fail(['message' => 'You have no permission to remove members from this group. ']);
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('groups', 'edit'))
return $this->fail(['message' => $this->is->getError()]);

$remove = [
'group_id' => $group_id,
'member_id' => $member_id];

if($this->groupmembers->where($remove)->first())
$this->groupmembers->where($remove)->delete();
return $this->respondDeleted('success');

}

public function get_members($id) {
if(!$this->us->is_owner($id, 'groups'))
return $this->fail(['message' => ' You cannot access this group. ']);

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('groups', 'view'))
return $this->fail(['message' => $this->is->getError()]);

$subquery = db_connect()->table('group_members')->select('member_id')->where('group_id', $id);
$members = $this->users->join('user_members', 'user_members.member_id = users.id')->select('users.id, concat(users.first_name, " ", users.last_name) as name, email')->where('user_members.user_id', $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid)->where('user_members.status', 'approved')->whereNotIn('users.id', $subquery)->findAll();
return $this->respond($members);
}


}
