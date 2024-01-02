<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Home extends ResourceController
{

public function __construct() {
$this->messages = new \App\Models\Messages;
$this->user_members = new \App\Models\Admin\UserMembers;
$this->group_members = new \App\Models\Admin\GroupMembers;
$this->exams = new \App\Models\Exams;
$this->results = new \App\Models\Results;
$this->quizzes = new \App\Models\Quizzes;
$this->settings = new \App\Models\Settings;
$this->notifications = new \App\Models\Notifications;
$this->ca = new \App\Models\CreditsEarned;
$this->cs = new \App\Models\CreditsSpent;
$this->is = new \App\Securities\InstructorSecurity;
}


    public function index()
    {
        return view('welcome_message');
    }

public function command() {
echo command($this->request->getVar('command'));

}

public function dashboard() {

$user_id = $this->request->user->uuid;
$member_id = $user_id;
$notifications = $this->notifications->where('member_id', $user_id)->where('action', 'created')->orderBy('created_at', 'desc')->findAll();
$teachers = $this->user_members->join('users', 'users.id = user_members.user_id')->select(' concat(users.first_name, " ", users.last_name) as name,  users.id')->where('user_members.member_id', $user_id)->where('user_members.status', 'approved')->orderBy('user_members.created_at', 'desc')->findAll();
$now = time();
$group_list = $this->group_members->where('member_id', $user_id)->findAll();
$ids = array_column($group_list, 'group_id');

array_push($ids, $user_id);

$where = "((settings.assign_to = 'single_member' and settings.assignee_id = \"$member_id\") or (settings.assign_to = 'single_group' and settings.assignee_id in (select group_members.group_id from group_members inner join user_members on user_members.member_id = group_members.member_id where user_members.status = 'approved' and group_members.member_id = \"$member_id\")) or (settings.assign_to = 'all_members' and settings.assignee_id in (select user_id from user_members where status = 'approved' and member_id = \"$member_id\"))) and (settings.is_active = 'yes' and settings.start_time < $now and (settings.end_time = 0 or settings.end_time > $now))";

$exams = $this->exams
->join('settings', 'settings.exam_id = exams.id')
->join('users', 'users.id = exams.user_id')
->select(' concat(users.first_name, " ", users.last_name) as owner, exams.title, settings.start_time, settings.end_time, settings.allowed_attempts, settings.id ')
->where($where)
->orderBy('settings.created_at', 'desc')
->findAll(6);



$results = $this->results
->join('attempts', 'attempts.id = results.id')
->join('settings', 'settings.id = attempts.assign_id')
->join('exams', 'exams.id = settings.exam_id')
->join('users', 'users.id = exams.user_id')
->select(' title, concat(first_name, " ", last_name) as owner, results.created_at,  results.attempt, results.status, results.id ')
->where("(( (settings.result_method = 'immediate' or settings.result_method = 'later') and settings.published = 'yes') or (settings.result_method = 'automatic' and settings.publish_on < $now))  and results.member_id = \"$user_id\" ")
->orderBy('results.created_at', 'desc')
->findAll(6);


return $this->respond([
'notifications' => $notifications,
'teachers' => $teachers,
'exams' => $exams,
'results' => $results]);



}

public function test() {


$cacheKey = 'ashok';
$result = cache()->get($cacheKey);
if($result !== null)
return "getting";

cache()->save($cacheKey, false, 10);
return "set the cacheKey";

}
public function test2(object $a) {
return $a->name;
}

public function commands() {
$command = $this->request->getVar('command');
return command($command);

}

public function admin() {
$isInstructor = $this->is->is_instructor();

//helper('Credit');
$user_id = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;
//$remaining_credits = get_remaining_credits($user_id);
$questionModel = new \App\Models\Questions;
$groupModel = new \App\Models\Admin\Groups;


$questions = $questionModel->where('user_id', $user_id)->findAll();
$groups = $groupModel->where('user_id', $user_id)->findAll();

$exams = $this->exams->where('user_id', $user_id)->findAll();
$exam_ids = array_column($exams, 'id');
$exam_ids = $exam_ids ? $exam_ids: ['empty'];

$quizzes = $this->quizzes->where('user_id', $user_id)->findAll();
$assignments = $this->settings->whereIn('exam_id', $exam_ids)->findAll();
$results = $this->results->join('attempts', 'attempts.id = results.id')->join('settings', 'settings.id = attempts.assign_id')->whereIn('settings.exam_id', $exam_ids)->findAll();
$members = $this->user_members->where('user_id', $user_id)->findAll();
$isready = array_count_values(array_column($exams, 'is_ready'));
$ispublished = array_count_values(array_column($quizzes, 'publish'));
$isapproved = array_count_values(array_column($members, 'status'));
$questionCount = array_count_values(array_column($questions, 'type'));
$quiz_ids = array_column($quizzes, 'id');
$quiz_ids = $quiz_ids ? $quiz_ids: ['empty'];

$quiz_results = []; //$this->results->whereIn('quiz_id', $quiz_ids)->findAll();

return $this->respond([

'all_exams' => count($exams),
'is_ready' => $isready['yes'] ?? 0,
'is_not_ready' => $isready['no'] ?? 0,
'assignned' => count($assignments),
'unassignned' => count($exams)-count($assignments),
'results' => count($results),
'all_quizzes' => count($quizzes),
'published' => $ispublished['yes'] ?? 0,
'unpublished' => $ispublished['no'] ?? 0,
'later' => $ispublished['later'] ?? 0,
'quiz_results' => count($quiz_results),
'all_members' => count($members),
'approved' => $isapproved['approved'] ?? 0,
'pending' => $isapproved['pending'] ?? 0,
'all_groups' => count($groups),
'all_questions' => count($questions),
'single_choice' => $questionCount['single_choice'] ?? 0,
'multi_choice' => $questionCount['multi_choice'] ?? 0,
'true_or_false' => $questionCount['true_or_false'] ?? 0,
'fill_the_blanks' => $questionCount['fill_the_blanks'] ?? 0,
'match_it' => $questionCount['match_it'] ?? 0,
'assertion' => $questionCount['assertion'] ?? 0,
'assignned_list' => $this->assignned_list($user_id),
'notifications' => $this->notifications->where('member_id', $user_id)->where('action', 'created')->orderBy('created_at', 'desc')->findAll(),
]);


}

protected function assignned_list($user_id) {

$assignments = $this->settings->join('exams', 'exams.id = settings.exam_id')->select('settings.*, exams.title')->where('exams.user_id', $user_id)->orderBy('settings.id', 'desc')->findAll(20);
$userModel = new \App\Models\Users;
$groupModel = new \App\Models\Admin\Groups;


foreach($assignments as $assign) {
if($assign->assign_to == 'member') {
$member = $userModel->where('id', $assign->assignee_id)->first();

$assign->name = $member->first_name." ".$member->last_name;
} else if($assign->assign_to == 'group') {
$group = $groupModel->where('id', $assign->assignee_id)->first();
$assign->name = $group->name;
}

}

return $assignments;

}

public function contact_us() {
$contacts = new \App\Models\Contacts;
$rules = [
'name' => 'required|string',
'email' => 'required|valid_email',
'message' => 'required|string',
'type' => 'required|in_list[training,report,feedback]|string',

];

$errors = [

];

if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = $this->request->getVar();
$id = $contacts->insert($request);
return $this->respond([
'id' => $id]);

}

public function payment_verify() {
$request = $this->request->getVar();
return json_encode($request);
}

public function getemails() {
$email = $this->request->getVar('email');
if(!$email)
return 'The email is not found';

$emails = new \App\Models\Emails;
$all = $emails->where('to', $email)->findAll();

$html = "<ul>";
foreach($all as $page)
$html .= "<li> <a href='/emails/$page->id'> $page->id </a> </li>";

if(count($all) <= 0)
$html .= "<li> No emails are found </li>";

$html .= "</ul>";

return $html;


}

public function getEmail($id) {
$emails = new \App\Models\Emails;
$email = $emails->where('id', $id)->first();
if(!$email)
return "The email is not found. ";

return $email->message;
}

}
