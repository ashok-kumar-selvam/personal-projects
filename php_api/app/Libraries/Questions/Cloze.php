<?php
namespace App\Libraries\Questions;
use \App\Libraries\Questions\BaseQuestion;
class Cloze extends BaseQuestion 
{

public function create($question) {
$question = (array) $question;
if(!$this->required($question, ['question', 'answer']))
return false;
$matchcount = preg_match_all('/-{3,}/', $question['question']);

if($matchcount == 0)
return $this->setError(' Any blank field (---) is required.');

if(!is_array($question['answer']))
return $this->setError(' The answer field should be an array. ');

if(count($question['answer']) != $matchcount)
return $this->setError(' The answer count should same as the blanks. ');

if($this->has_empty($question['answer']))
return $this->setError(' The answer field should not contain empty or null values. ');

return true;
}

public function correction($q, $ans) {

$return = $ans;

if(!$this->required($q, ['question', 'point', 'mpoint', 'answer']))
return false;

if(!$this->required($ans, ['answer']))
return false;

if(!is_array($ans->answer))
return $this->setError(' There is some error in answer. ');

if(count($ans->answer) <= 0) 
return $return;


if(isset($q->settings->point_type) && $q->settings->point_type == 'partial') {
$fraction = $q->point/count($q->answer);

foreach($ans->answer as $index => $value)
$result = (isset($q->answer[$index]) && $q->answer[$index] == $value) ? ($return->point += $fraction): ($return->is_correct = 'partial');

if($return->point > 0 && $return->is_correct != 'partial')
$return->is_correct = 'yes';

if(is_float($return->point))
$return->point = round($return->point, 2);
return $return;
}

if($q->answer == $ans->answer) {
$return->point = $q->point;
$return->is_correct = 'yes';
} else {
$return->point = $q->mpoint;
$return->is_correct = 'no';
}
return $return;
}


}
