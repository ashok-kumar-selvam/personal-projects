<?php
namespace App\Libraries\Uploads;

class BaseValidate 
{

protected $error;
protected $file = 'test.txt';
protected $type;
protected $questions = [];
protected $validated = false;
public function __construct($file, $type) {
//$this->error = 'json is not found';
//$classes = get_declared_classes();
//if(in_array("App\Libraries\Uploads\JSON", $classes)) {


//}

$this->file = $file;

$this->type = $type;
}


public function setError($error) {
if(is_iterable($error)) {
foreach($error as $key => $val) {
$length = mb_strlen($key);
$words = explode("/", $key);
if(count($words) == 2) {
$words[1]++;
$key = "question ".$words[1];
} else if(count($words) > 2) {

$words[1]++;
$key = "question ".$words[1].".".$words[2];
}


$this->error = "$key : $val";

break;
}

} else {
$this->error = $error;
}
return false;
}

public function getError() {
return $this->error;
}

public function arrayToJson($array) {
$newArray = [];
foreach($array as $key => $value) {
array_push($newArray, $value);
}
return json_encode($newArray);

}

public function setQuestions(array $questions) {
$this->questions = $questions;
}

public function getQuestions() {
if(!$this->validated) {
throw new \Exception("Warning! The data is not been validated");
return false;
} else {

return array_map(function($q) {
return (array) $q;
}, $this->questions);
}
}

}