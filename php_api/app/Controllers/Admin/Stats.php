<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Stats extends BaseController
{


public function __construct() {
$this->exams = new \App\Models\Exams;
$this->settings = new \App\Models\Settings;
$this->results = new \App\Models\Results;
$this->quizzes = new \App\Models\Quizzes;
}

public function exams($user_id) {
$exams = $this->exams->where('user_id', $user_id)->findAll();
$exam_ids = array_column($exams, 'id');
$exam_ids = $exam_ids ? $exam_ids: ['empty'];
$assignments = $this->settings->whereIn('exam_id', $exam_ids)->findAll();
$results = []; //$this->results->whereIn('entity_id', $exam_ids)->findAll();
$isready = array_count_values(array_column($exams, 'is_ready'));
return [
'all' => count($exams),
'is_ready' => $isready['yes'] ?? 0,
'is_not_ready' => $isready['no'] ?? 0,
'assignned' => count($assignments),
'unassignned' => count($exams)-count($assignments),
'results' => count($results)];
}

public function quizzes($user_id) {
$quizzes = $this->quizzes->where('user_id', $user_id)->findAll();
$ispublished = array_count_values(array_column($quizzes, 'publish'));
$quiz_ids = array_column($quizzes, 'id');
$quiz_ids = $quiz_ids ? $quiz_ids: ['empty'];
$results = $this->results->whereIn('quiz_id', $quiz_ids)->findAll();

return [
'all' => count($quizzes),
'published' => $ispublished['yes'] ?? 0,
'unpublished' => $ispublished['no'] ?? 0,
'later' => $ispublished['later'] ?? 0,
'results' => count($results)];
}

}
