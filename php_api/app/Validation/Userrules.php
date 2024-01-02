<?php

namespace App\Validation;

class Userrules
{

public function examValidate($exam_id, string $fields, array $data, &$error = null) {
$exams = new \App\Models\Exams;
$exam = $exams->find($exam_id);
if(!$exam) {
$error = lang("Unable to find the exam");
return false;
} else if($exam->is_ready == 'no') {
$error = lang("This exam is not ready yet");
return false;
}
return true;

}

public function settingValidate($setting_id, string $fields, array $data, &$error = null) {
$settings = new \App\Models\Settings;
$setting = (object) $settings->join('exams', 'exams.id = settings.exam_id')->select('settings.*')->where('settings.id', $setting_id)->first();
if(!$setting) {
$error = lang("Unable to find the settings for the exam");
return false;
} else if(($setting->start_time/1000) > time()) {
$error = lang("The exam has not started yet. It will be available after ".date("jS F Y, g:i A", $setting->start_time));
return false;
} else if(($setting->end_time/1000) < time()) {
$error = lang("The exam has completed");
return false;
}
return true;
}

public function is_assignee($assignee_id, string $fields, array $data, &$error = null) {
if(!$assignee_id)
return true;
$users = new \App\Models\Users;
$groups = new \App\Models\Admin\Groups;
$exams = new \App\Models\Exams;
$user_members = new \App\Models\Admin\UserMembers;
$assignments = new \App\Models\Settings;
if(isset($data['id'])) {
$assignment = $assignments->where('id', $data['id'])->first();
if(!$assignment) {
$error = lang(' Unable to find the assignment. ');
return false;
} else {
$exam = $exams->where('id', $assignment->exam_id)->first();
}

} else {
$exam = $exams->where('id', $data['exam_id'])->first();
}

if(!$exam) {
$error = lang('Invalid exam');
return false;
}


if($data['assign_to'] == 'single_member') {
$member = $users->where('id', $assignee_id)->first();
if(!$member) {
$error = lang('Invalid member');
return false;
} else if(!$user_members->where('member_id', $assignee_id)->where('user_id', $exam->user_id)->first()) {
$error = lang('You have no permition to assign an exam to this member');
return false;
}
return true;
} else if($data['assign_to'] == 'single_group') {
if(!$groups->where('id', $assignee_id)->first()) {
$error = lang('Invalid group');
return false;
}
return true;
}
$error = lang('Something went wrong!');
return false;


}

public function has_questions($exam_id, string $fields, array $data, &$error = null) {
$questions = new \App\Models\QuestionSheet;
$exam_questions = $questions->where('entity_id', $exam_id)->countAllresults();
if($exam_questions <= 0) {
$error = lang('The exam has no questions. Please add some questions to assign this exam');
return false;
}
return true;
}

public function isExists($entity_id, string $fields, array $data, &$error = null) {
$exams = new \App\Models\Exams;
$quizzes = new \App\Models\Quizzes;
$entity = $exams->where('id', $entity_id)->first() ?? $quizzes->where('id', $entity_id)->first();
if(!$entity) {
$error = lang(' The quiz or exam is not found ');

return false;
} 


return true;
}

public function isAllowedType($type, string $fields, array $data, &$error = null) {

$exams = new \App\Models\Exams;
$quizzes = new \App\Models\Quizzes;

if($quizzes->where('id', $data['entity_id'])->first()) {
$allowed = ['single_choice', 'multi_choice', 'true_or_false'];
if(!in_array($type, $allowed)) {
$error = lang(' The quiz can only contain single choice, multi choice or true or false questions. ');
return false;
}
return true;
} else if($exams->where('id', $data['entity_id'])->first()) {
$array = ['single_choice', 'multi_choice', 'true_or_false', 'match_it', 'fill_the_blanks', 'descriptive', 'cloze', 'error_correction'];
if(!in_array($type, $array)) {
$error = lang(" $type type question is not allowed ");
return false;
}
return true;

}

$error = lang(" Unknown entity  only quiz and exam are supported. ");
return false;
}

public function checkType($options, string $fields, array $data, &$error = null) {
if($data['type'] == "single_choice" || $data['type'] == "true_or_false" || $data['type'] == "multi_choice") {
if(!is_array($data['options'])) {
$error = lang(' The options must be an array. Please contact the admin for more info. ');
return false;
}
return true;
}
return true;
}

public function checkAnswer($answer, string $fields, array $data, &$error = null) {
if($data['type'] == "single_choice" || $data['type'] == "true_or_false" || $data['type'] == "multi_choice") {
if(empty($answer) || is_null($answer)) {
$error = lang(' the answer field is required. ');
return false;
}
return true;
}

return true;
}

public function required_if($allowed_access, string $fields, array $data, &$error = null) {
$params = explode(',', $fields);
if(!isset($data[$params[0]]))
return true;
$array = array_slice($params, 1);
if(in_array($data[$params[0]], $array) && !$allowed_access) {

return false;
}
return true;

}

public function is_owner($id, string $fields, array $data, &$error = null) {
$options = explode(',', $fields);

if(!isset($options[0]) || !isset($options[1])) {
$error = lang(' Please provide the owner id. ');
return false;
}
$db = db_connect();
$request = \Config\Services::request();

switch($options[1]) {
case "exams":
$query = $db->table('exams')->where('id', $id)->get();
$result = $query->getRow();


break;
case "assignments":
$query = $db->table('settings')->join('exams', 'exams.id = settings.exam_id')->select('user_id')->where('settings.id', $id)->get();
$result = $query->getRow();
break;
case "quizzes":
$query = $db->table('quizzes')->where('id', $id)->get();
$result = $query->getRow();

break;
case "groups":
$query = $db->table('groups')->where('id', $id)->get();
$result = $query->getRow();
break;
case "questions":
$query = $db->table('questions')->where('id', $id)->get();
$result = $query->getRow();
break;
default:
return true;
}

if(!$result)
return true;

if($result->user_id != $request->user->uuid) {
if(!$request->user->admin_id || $request->user->admin_id != $result->user_id) {
$error = lang('You have no permission to access this exam. ');
return false;
}
}
return true;
}

}
