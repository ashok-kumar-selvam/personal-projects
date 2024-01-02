<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Demo extends Seeder
{
    public function run()
    {
$examModel = new \App\Models\Exams;

$questionModel = new \App\Models\Questions;
$questions = [
['quiz_id' => 'ghpq', 'type' => 'multi_choise', 'question' => 'find the fraction which is equal to 50 percentage.', 'options' => ['1/2', '4/8', '11/13', '15/12', '2/3'], 'answer' => ['1/2', '4/8']],
['quiz_id' => 'ghpq', 'type' => 'multi_choise', 'question' => 'find the prime numbers', 'options' => [13,12,17,18], 'answer' => [13,17]],
['quiz_id' => 'ghpq', 'type' => 'multi_choise', 'question' => 'find the odd numbers', 'options' => [13,12,17,18], 'answer' => [13,17]],
];

$ids = array();
foreach($questions as $question) {
$questionEntity = new \App\Entities\Questions($question);
array_push($ids, $questionModel->insert($questionEntity));
}

$examEntity = new \App\Entities\Exams([
'title' => 'multi_choise quiz2',
'description' => 'Only multi choise questions',
'subject' => 'demo',
'is_ready' => 'yes',
'questions' => $ids]);
$exam_id = $examModel->insert($examEntity);
$settings = new \App\Models\Settings;
$settings->insert([
'exam_id' => $exam_id,
'start_time' => time(),
'end_time' => time()+(3600*24),
'message' => 'only multi choise']);


    }
}
