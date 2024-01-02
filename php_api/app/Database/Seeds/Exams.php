<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Exams extends Seeder
{
    public function run()
    {


$questions = new \App\Models\Questions;

$exams = new \App\Models\Exams;
$entity = new \App\Entities\Exams([
'title' => 'demo exam '.($exams->countAllResults()+1),
'subject' => 'demo',
'description' => 'testing demo exam',
'is_ready' => 'yes',
'questions' => $questions->findColumn('id')]);
$exams->insert($entity);
    }
}
