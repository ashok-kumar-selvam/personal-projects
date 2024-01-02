<?php

namespace App\Controllers\Member;

use CodeIgniter\RESTful\ResourceController;

class All extends ResourceController
{
protected $format = 'json';

public function __construct() {
$this->settings = new \App\Models\Settings;
$this->db = db_connect();
$this->questions = new \App\Models\Questions;
$this->QuizResults = new \App\Models\QuizResults;
$this->results = new \App\Models\Results;
$this->quizzes = new \App\Models\Quizzes;
}


    public function exams()
    {

$now = time();
$member_id = $this->request->user->uuid;

$where = "((settings.assign_to = 'single_member' and settings.assignee_id = \"$member_id\") or (settings.assign_to = 'single_group' and settings.assignee_id in (select group_members.group_id from group_members inner join user_members on user_members.member_id = group_members.member_id where user_members.status = 'approved' and group_members.member_id = \"$member_id\")) or (settings.assign_to = 'all_members' and settings.assignee_id in (select user_id from user_members where status = 'approved' and member_id = \"$member_id\"))) and (settings.is_active = 'yes' and settings.start_time < $now and (settings.end_time = 0 or settings.end_time > $now))";
$exams = $this->settings
->join('exams', 'exams.id = settings.exam_id')
->join('users', 'users.id = exams.user_id')
->select('exams.title, exams.subject, exams.description, concat(users.first_name, " ", users.last_name) as owner, settings.*')
->where($where)
->orderBy('settings.created_at', 'desc')
->findAll();

foreach($exams as $exam) {
$exam->questions = $this->db->table('question_sheet')->where('entity_id', $exam->exam_id)->countAllResults();
}


return $this->respond([
'exams' => $exams]);


    }


    public function quizzes()
    {
$user_members = new \App\Models\Admin\UserMembers;
$teacher_ids = $user_members->where('member_id', $this->request->user->uuid)->where('status', 'approved')->findColumn('user_id') ?? ['abcd'];
$quizzes = $this->quizzes
->join('users', 'quizzes.user_id = users.id')
->select('concat(users.first_name, " ", users.last_name) as admin, quizzes.* ')
->whereIn('quizzes.user_id', $teacher_ids)
->where("quizzes.publish", 'yes')
->findAll();

foreach($quizzes as $quiz) {
$quiz->questions = $this->questions->join('question_sheet', 'questions.id = question_sheet.question_id')->where('question_sheet.entity_id', $quiz->id)->countAllResults();
}

return $this->respond([
'quizzes' => $quizzes]);
    }


    public function results()
    {
$type = $this->request->getVar('type');
if(!$type)
return $this->fail(['message' => 'Please tell which result do you want? exam or quiz. ']);
if($type == 'exam') {
$now = time();
$user_id = $this->request->user->uuid;
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
} else if($type == 'quiz') {
$results = $this->QuizResults
->join('quizzes', 'quizzes.id = quiz_results.quiz_id')
->join('users', 'users.id = quizzes.user_id')
->select(' quizzes.title, quiz_results.attempt, concat(users.first_name, " ", users.last_name) as admin_name, quiz_results.created_at as completed_on, quiz_results.id ')
->where('quiz_results.member_id', $this->request->user->uuid)
->orderBy('quiz_results.created_at', 'desc')
->findAll();

} else {
return $this->fail(['message' => "Please select exam or quiz. you have selected $type"]);
}


return $this->respond($results);
    }

}
