<?php

namespace App\Libraries\Uploads;
use \App\Libraries\Uploads\BaseValidate;

use Opis\JsonSchema\{ Validator, ValidationResult, Errors\ErrorFormatter,};

class JSON extends BaseValidate
{

public function validate() {
$file = is_string($this->file) ? json_decode($this->file): json_decode(file_get_contents($this->file));
$schema = ($this->type == 'quiz') ? WRITEPATH.'Schemas/Quiz/questions.json': WRITEPATH.'Schemas/Exam/questions.json';
$schema = json_decode(file_get_contents($schema));


$validator = new Validator();
$result = $validator->validate($file, $schema);
if (!$result->isValid()) {
$error = $result->error();
$formatter = new ErrorFormatter();

$this->validated = false;
$this->setError($formatter->format($error, false));
} else {
$this->validated = true;

$this->setQuestions($file);
}


return $result->isValid();
}





private function is_json($string) {
json_decode($string);
return (json_last_error() == JSON_ERROR_NONE);
}

} 