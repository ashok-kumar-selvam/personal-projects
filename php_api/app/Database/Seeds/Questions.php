<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Questions extends Seeder
{
    public function run()
    {
$questionModel = new \App\Models\Questions;

$questions = [
['quiz_id' => 1, 'type' => 'single_choise', 'question' => 'what is 3+3?', 'options' => [3,4,5,6], 'answer' => '6'],
['quiz_id' => 1, 'type' => 'single_choise', 'question' => 'what is 3*3?', 'options' => [7,8,9,10], 'answer' => '9'],
['quiz_id' => 1, 'type' => 'single_choise', 'question' => 'what is 10/2?', 'options' => [2,3,4,5], 'answer' => '5'],
['quiz_id' => 1, 'type' => 'single_choise', 'question' => 'what is 17-12?', 'options' => [5,6,7,8,], 'answer' => '5'],
['quiz_id' => 1, 'type' => 'single_choise', 'question' => 'what is the squire root of 36?', 'options' => [5,6,7,8], 'answer' => '6'],
['quiz_id' => 1, 'type' => 'true_or_false', 'question' => 'The squire of 7 is 49.', 'options' => ['true', 'false'], 'answer' => 'true'],
['quiz_id' => 1, 'type' => 'true_or_false', 'question' => '40 is 38.75 percentage of 120.', 'options' => ['true', 'false'], 'answer' => 'false'],
['quiz_id' => 1, 'type' => 'multi_choise', 'question' => 'Find the prime numbers.', 'options' => [17,18,19,20], 'answer' => [17,19]],
];
foreach($questions as $question) {
$entity = new \App\Entities\Questions($question);
$questionModel->insert($entity);
}



    }
}
