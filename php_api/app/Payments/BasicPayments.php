<?php
namespace App\Payments;

class BasicPayments {
protected $error = 'no error';
public function getError() {
return $this->error;
}

public function setError($error) {
$this->error = $error;
}

public function check_required() {
foreach($this->required as $key => $value) {
if(!isset($this->input_data[$key]) || is_null($this->input_data[$key])) {
$this->error = " the $key field is required. ";
return false;
}
}
return true;
}

}
