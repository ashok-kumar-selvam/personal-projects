<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Settings extends Seeder
{
    public function run()
    {
$exams = new \App\Models\Exams;
$settings = new \App\Models\Settings;
$id = $exams->orderBy('id', 'desc')->first()->id;

$settings->insert([
'exam_id' => $id,
'start_time' => time(),
'end_time' => time()+(3600*24)*3,
'message' => 'testing settings']);

    }
}
