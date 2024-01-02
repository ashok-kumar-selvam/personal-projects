<?php
namespace App\Libraries\Questions;
use \App\Libraries\Questions\BaseQuestion;
class ErrorCorrection extends BaseQuestion 
{

public function create($question) {
$question = (array) $question;
if(!$this->required($question, ['question', 'answer']))
return false;

return true;
}

public function correction($question, $answer) {
$q = (array) $question;
$ans = (array) $answer;
$return = (array) [
'id' => $ans['id'],
'has_answered' => 'no',
'is_correct' => 'no',
'point' => 0,
];
if(empty($ans['answer']) || is_null($ans['answer'])) {
$return->has_answered = 'no';
return $return;
}


if(!is_array($q['answer'])
return $this->setError(' The answer array is not an array. ');

if(in_array(trim($ans['answer']), $q['answer'])) {
$return->is_correct = 'yes';
$return->point = $q['point'];
} else {
$return->point = $q['mpoint'];
$return->is_correct = 'no';

}
return $return;

}

}
