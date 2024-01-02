<?php
namespace App\Libraries\Questions;

class BaseQuestion {
protected $error;
public function setError($error) {
$this->error = $error;
return false;
}


public function getError() {
return $this->error;
}


public function required($data, $array) {
return true;

foreach($array as $value) 
if(!isset($data[$value]) || empty($data[$value]))
return $this->setError(" $value field is required.");

return true;
}

public function has_empty($array) {
foreach($array as $value)
if(empty($value) || is_null($value))
return true;

return false;
}


}
