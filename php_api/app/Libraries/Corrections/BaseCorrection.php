<?php

namespace App\Libraries\Corrections;
class BaseCorrection {
private $error;

public function __construct() {
$this->answers = new \App\Models\Answers;
$this->attempts = new \App\Models\Attempts;
$this->questions = new \App\Models\Questions;

}


public function setError($error) {
$this->error = $error;
return false;
}

public function getError() {
return $this->error;
}

}
