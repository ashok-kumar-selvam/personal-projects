<?php

namespace App\Libraries\Corrections;
use \App\Libraries\Corrections\BaseCorrection;

class Corrections extends BaseCorrection {
protected $status = 'completed';

public function single_choice(Object $question, Object $answer) {
if(!is_string($question->answer)) 
return $this->setError(" The answer provided by the admin seems to have some errors.  ");

if(!is_string($answer->answer))
return $this->setError(' The answer given by the student is not valid. ');
if(empty($answer->answer)) {
$answer->has_answered = 'no';
$answer->is_correct = 'unanswered';
return $answer;
}


if(trim($answer->answer) == trim($question->answer)) {
$answer->point = $question->point; 
$answer->is_correct = 'yes';
} else {

if($question->mpoint > 0)
$answer->point = -$question->mpoint;
$answer->is_correct = 'no';

}

return $answer;
}

public function multi_choice(object $question, object $answer) {
if(empty($answer->answer)) {
$answer->has_answered = 'no';
$answer->is_correct = 'unanswered';
return $answer;
}


if(isset($question->settings->point_type) && $question->settings->point_type == 'partial') {
$answer->point = 0;
$fraction = $question->point/count($question->answer);
foreach($answer->answer as $given) 
$result = (in_array($given, $question->answer)) ? ($answer->point += $fraction): ($answer->is_correct = 'partial');

if($answer->point > 0 && $answer->is_correct != 'partial')
$answer->is_correct = 'yes';

if(is_float($answer->point))
$answer->point = round($answer->point, 2);
return $answer;
}


$array1 = $answer->answer;
$array2 = $question->answer;
sort($array1);
sort($array2);


if($array1 == $array2) {
$answer->point = $question->point; 
$answer->is_correct = 'yes';
} else {
if($question->mpoint > 0)
$answer->point = -$question->mpoint;
$answer->is_correct = 'no';

}


return $answer;

}

public function fill_the_blanks(object $question, object $answer) {

if(empty($answer->answer)) {
$answer->has_answered = 'no';
$answer->is_correct = 'unanswered';
return $answer;
}

if(in_array($answer->answer, $question->answer)) { 
$answer->point = $question->point; 
$answer->is_correct = 'yes';
} else {
if($question->mpoint > 0)
$answer->point = -$question->mpoint;
$answer->is_correct = 'no';

}
return $answer;
}

public function descriptive(object $question, object $answer) {
$answer->is_correct = 'review';

if(isset($answer->answer->is_evaluated) && $answer->answer->is_evaluated == 'yes') {
if($answer->point > 0) {

$answer->is_correct = 'yes';

} else {
$answer->is_correct = 'no';
}
} else if(is_string($answer->answer)) {
$answer->answer = [
'is_evaluated' => 'no'];
$this->status = 'incomplete';
}

return $answer;
}

public function match_it(object $question, object $answer) {

$total_point = $question->point;
$fraction = $question->point/count($question->answer);

$questions = array_column($answer->answer, 'question');
$answer->point = 0;
foreach($question->answer as $rAnswer) {
$index = array_search($rAnswer->question, $questions);
if($index === false)
continue;

$gAnswer = $answer->answer[$index];
if($gAnswer->question == $rAnswer->question && $gAnswer->answer == $rAnswer->answer) {
$answer->point += $fraction;

} else {
if(isset($question->settings->point_type) && $question->settings->point_type == 'partial') {
$answer->is_correct = 'partial';
continue;
}

if($question->mpoint > 0)
$answer->point = -$question->mpoint;
break;
}

}

if($answer->point > 0 && $answer->is_correct != 'partial')
$answer->is_correct = 'yes';

if(is_float($answer->point))
$answer->point = round($answer->point, 2);

return $answer;
}

public function cloze($q, $ans) {
$return = $ans;

if(!is_array($ans->answer))
return $this->setError(' There is some error in answer. ');

if(count($ans->answer) <= 0) {
$return->is_correct = 'unanswered';
$answer->has_answered = 'no';
return $return;
}

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

public function run($result_id) {

$answers = $this->answers->where('result_id', $result_id)->where('has_attempted', 'yes')->orderBy('number', 'asc')->findAll();

$assign = $this->attempts->join('settings', 'settings.id = attempts.assign_id')->where('attempts.id', $result_id)->first();
$newarray = [];
foreach($answers as $answer) {
$question = $this->questions->where('id', $answer->question_id)->first();

if(empty($answer->answer) && $answer->answer !== 0) {
$answer->has_answered = 'no';
$entity = new \App\Entities\Answers($answer->toArray());
$this->answers->update($answer->id, $entity);
continue;
}

switch($question->type) {
case "single_choice":
case "true_or_false":
$answer = $this->single_choice($question, $answer);
break;
case "multi_choice":
$answer = $this->multi_choice($question, $answer);
break;
case "fill_the_blanks":

$answer = $this->fill_the_blanks($question, $answer);
break;
case "error_correction":
if($answer->answer == $question->answer) {
$answer->is_correct = 'unanswered';
$answer->has_answered = 'no';
} else {
$answer = $this->fill_the_blanks($question, $answer);
}

break;
case "descriptive":
$answer = $this->descriptive($question, $answer);
break;
case "match_it":
$answer = $this->match_it($question, $answer);

break;
case "cloze":


$answer = $this->cloze($question, $answer);
break;

} // end of switch
if(!$answer)
return false;



if($assign->mpoint > 0) {
if($answer->is_correct == 'no')
$answer->point = -(($question->point/100)*$assign->mpoint);
}


$entity = new \App\Entities\Answers($answer->toArray());
$this->answers->update($answer->id, $entity);
} // end of foreach

return $this->status;
}


}
