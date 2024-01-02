<?php

namespace App\Libraries\Uploads;
use \App\Libraries\Uploads\BaseValidate;

class Excel extends BaseValidate 
{
protected $file;

protected $type;
public function __construct($file, $type) {
        $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadSheet = $Reader->load($file);
        $excelSheet = $spreadSheet->getActiveSheet();
$array = $excelSheet->toArray();
$this->file = $array;
$this->type = $type;
}

public function validate() {

$file = $this->file;
if(count($file) <= 1) {
$this->setError("The document seems to be empty");
return false;
}


foreach($file as $column => $row) {

$row = $this->sanitize($row);


if($column == 0 || $column%2 == 0)
continue;

if(empty($row))
continue;



$types = ["single_choice", "multi_choice", "true_or_false", "match_it", "fill_the_blanks", "descriptive"];


if(($column == 1 || $column%2 == 1) && !in_array($row[4], $types)) {
$this->setError("The document is not properly formatted. Please recheck it and try again. ");
return false;
}

$row2 = $file[$column+1] ?? [];
$row2 = $this->sanitize($row2);

if(count($row) < 6) {
$this->setError("question ".($column+1/2)." : Please provide proper values ");
return false;
}

$questionNo = $row[0] ?? floor(($column+1)/2);
$point = $row[1] ?? 1;
$mpoint = $row[2] ?? 0;
$explanation = $row[3] ?? "";
$type = $row[4];
$question = $row[5];

if(($type == "match_it" || $type == "fill_the_blanks" || $type == "descriptive") && $this->type == "quiz") {
$this->setError("The quiz does not support ".str_replace('_', ' ', $type)." questions. Please try exam instead.");
return false;
}

if($mpoint > $point) {
$this->setError("question $questionNo : The mpoint cannot be greater than point");
return false;
}



if(is_null($question) || empty($question)) {
$this->setError("The question cannot be empty. Please check the question number $questionNo ");
return false;
}

switch($row[4]) {
case "single_choice":
case "multi_choice":
case "true_or_false":
$result = $this->choise($row, $row2, $questionNo);
if(!$result)
return false;

break;
case "match_it":
$result = $this->match($row, $row2, $questionNo);
if(!$result)
return false;


break;
case "fill_the_blanks":

if(count($row) < 7) {
$this->setError("question $questionNo : Please provide at least one answer. ");
return false;
}
$options = array_slice($row, 6);


if(in_array(null, $options, true)) {

$this->setError("question $questionNo : Please don't provide empty values in answer");

return false;
}


break;
case "descriptive":

break;
default:
$this->setError("Unknown question type ".$row[4]);

return false;
}

}
$this->validated = true;
return true;
}

public function getQuestions() {
if(!$this->validated) {
throw new \Exception("The file is not validated yet");
return false;
}

$questions = [];
foreach($this->file as $column => $row) {

if($column == 0 || $column%2 == 0)
continue;

$question = [];
$row = $this->sanitize($row);

if(empty($row))
continue;

$row2 = $this->file[$column+1] ?? [];

$row2 = $this->sanitize($row2);
$row1 = $row;

$question['point'] = $row[1] ?? 1;
$question['mpoint'] = $row[2] ?? 0;
$question['explanation'] = $row[3] ?? "";
$question['type']  = $row[4];
$question['question'] = $row[5];
switch($row[4]) {
case "single_choice":
case "true_or_false":
$question['options'] = array_slice($row1, 6);
$answers = array_slice($row2, 6);
$answer = array_search('yes', $answers);
$answer = $question['options'][$answer];
if($row[4] == 'true_or_false') {
$question['options'] = ['true', 'false']; // the automatic convertion changed the options array to bool resulted in empty display in the answer page.
$question['answer'] = $answer == true ? "true": "false";

} else {
$question['answer'] = (string) $answer;
}

break;
case "multi_choice":
$question['options'] = array_slice($row1, 6);
$answers = array_slice($row2, 6);
$q = count($question['options']);

$question['answer'] = [];
for($i = 0; $i < count($answers)-1; $i++) {
if($answers[$i] == 'yes')
array_push($question['answer'], $question['options'][$i]);

}
break;
case "match_it":
$options = array_slice($row1, 6);
$answers = array_slice($row2, 6);

foreach($options as $key => $val) {

$question['answer'][$val] = $answers[$key];
}


break;
case "fill_the_blanks":
$question['answer'] = array_slice($row, 6);
break;
case "descriptive":

break;
default:
throw new \Exception("Unknown question type ".$row[4]);
}
array_push($questions, $question);

}


return $questions;
}

public function choise(array $row1, array $row2, $no) {
$qno = "question $no : ";
if(count($row1) < 8) {

$this->setError($qno."The ".$row[4]." question must contain a question with at least 2 options. ");
return false;
}

if(count($row2) < 7) {
$this->setError($qno." You should provide at least one answer.");
return false;
}

if(empty($row1[5]) || is_null($row1[5])) {
$this->setError($qno." The question cannnot be empty");
return false;
}

$options = array_slice($row1, 6);
$answers = array_slice($row2, 6);

$answer = array_search('yes', $answers);
if($answer === false) {
$this->setError($qno." You must provide at least one 'yes' value below the options. ");
return false;
}

if(!isset($row1[$answer]) || empty($row1[$answer]) || is_null($row1[$answer])) {
$this->setError($qno." You should mark a valid field as the answer. It seems that you have provided 'yes' below an empty cell");

return false;
}

$answer_count = count(array_filter($answers, function($a) {
return $a == 'yes';
}));



if(($row1[4] == 'single_choice' || $row1[4] == 'true_or_false') && $answer_count != 1) {
$this->setError($qno." The single choise and true or false questions can only contain one answer. It seems that it has $answer_count answers");
return false;
} else if($row1[4] == 'multi_choice' && $answer_count < 1) {
$this->setError($qno." The multi choice questions should contain at least one answer.");
return false;
}

if($row1[4] == 'true_or_false' && $row1[$answer] != true || $row1[$answer] != false) {
$this->setError($qno." The true or false question must contain options as true, false and not other test");

}

return true;
}

private function match($row1, $row2, $qno) {
$qno = "question $qno : ";

if(count($row1) < 9 || count($row2) < 9) {
$this->setError($qno." The Match it question should contain at least 2 question and answer.");
return false;
}

$options = array_slice($row1, 6);
$answers = array_slice($row2, 6);

if(count($options) != count($answers)) {
$this->setError($qno." Provide same number of questions and answers. ");
return false;
}

if(in_array(null, $options, true) || in_array(null, $answers, true)) {
$this->setError($qno." empty values are not allowed ");
return false;
}

return true;
}

private function sanitize($array) {
for($i = count($array)-1; $i >= 0; $i--) {
if(is_null($array[$i])) {
array_pop($array);

} else {
return $array;
}

}

return $array;
}

}
