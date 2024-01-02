<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Quizzes extends Seeder
{
    public function run()
    {

$quizzes = new \App\Models\Quizzes;
$quizzes->insert([
'title' => 'Demo quiz 1',
'subject' => 'demo',
'description' => 'this is a demo quiz.']);

    }
}
