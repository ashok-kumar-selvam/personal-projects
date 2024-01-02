<?php

namespace App\Libraries\Uploads;
use \App\Libraries\Uploads\XML;
use \App\Libraries\Uploads\JSON;

use Yosymfony\Toml\Toml;

class Validator {
protected $type;
protected $error = 'no error';
protected $file;

private $questions;
public function __construct($file, $type) {
$this->file = $file;
$this->type = $type;
}

public function run() {
$ext = $this->file->guessExtension();
$ext = (!$ext) ? $this->file->getClientExtension(): $ext;

switch($ext) {
case 'xml':
$this->error = "The xml format is not supported now.";
return false;
$xml = new XML($this->file, $this->type);
$result = $xml->validate();
if(!$result)
$this->error = $xml->getError();
return $result;
break;
case "json":
$json = new JSON($this->file, $this->type);
$result = $json->validate();
if(!$result) {
$this->error = $json->getError();
} else {
return $this->setQuestions($json->getQuestions());
}

return $result;
break;
case "xlsx":
$excel = new \App\Libraries\Uploads\Excel($this->file, $this->type);
$result = $excel->validate();
if(!$result) {
$this->error = $excel->getError();
} else {
return $this->setQuestions($excel->getQuestions());
}

return $result;
break;
case "toml":
$toml = new \App\Libraries\Uploads\Tml($this->file, $this->type);
$result = $toml->validate();
if(!$result) {
$this->error = $toml->getError();
} else {
return $this->setQuestions($toml->getQuestions());
}

return $result;
break;
case "docx":

$word = new \App\Libraries\Uploads\Text($this->file, $this->type);
$result = $word->validate();
if(!$result) {
$this->error = $word->getError();
} else {
return $this->setQuestions($word->getQuestions());
}

return $result;
break;
case "txt":
$txt = new \App\Libraries\Uploads\Txt($this->file, $this->type);
$result = $txt->validate();
if(!$result) {
$this->error = $txt->getError();
} else {
return $this->setQuestions($txt->getQuestions());
}

return $result;
break;
default:
$this->error = 'We do not support the ('.$ext.') file type';
return false;
}

}

public function getError() {
return 'Uploading error: '.$this->error;
}

public function setQuestions(array $questions) {
foreach($questions as $key => $question) {
$question = (array) $question;
$qno = "Question ".($key+1)." : ";
switch($question['type']) {
case "single_choise":
case "true_or_false":
if(!in_array($question['answer'], $question['options'])) {
$this->error = $qno." The answer is not found in the options array";
return false;
}
break;
case "multi_choise":
for($i = 0; $i < count($question['answer']); $i++) {
if(!in_array($question['answer'][$i], $question['options'])) {
$this->error = $qno." The value ".$question['answer'][$i]." is not found in the options";
return false;
}

}
break;
case "match_it":

$options = array_keys($this->toArray($question['answer']));
$answers = array_values($this->toArray($question['answer']));

if(count($options) < 2 || count($answers) < 2) {
$this->error = $qno." Please provide atleast two set of questions and answers. ";
return false;
}

if(!$this->has_dupes($options) || !$this->has_dupes($answers)) {
$this->error = $qno." There are some duplicate values in the answers.";
return false;
}
$ans = (array) $question['answer'];
$questions[$key]['answer']  = [];

foreach($ans as $ind => $value) {
array_push($questions[$key]['answer'], ['question' => $ind, 'answer' => $value]);
}

break;
}


}

$this->questions = $questions;
return true;
}

public function getQuestions() {
return $this->questions;
}

public function has_dupes($array) {
$new = [];
foreach ($array as $index => $val) {
if(in_array($val, $array)) 
return true;
array_push($val, $new);

}
return false;
}

public function toArray($ar) {
return (array) $ar;
}

public function getCount() {
if(!is_countable($this->questions))
return 0;

return count($this->questions);
}


}
