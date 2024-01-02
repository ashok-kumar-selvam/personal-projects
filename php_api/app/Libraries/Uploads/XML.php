<?php

namespace App\Libraries\Uploads;
use \App\Libraries\Uploads\BaseValidate;
class XML extends BaseValidate 
{


protected function errorHandler($errno, $errstr, $errfile, $errline) {


if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::load()")>0))
{
//throw new \DOMException($errstr);

}
else {
return false;
}

}

public function validate() {

libxml_use_internal_errors(true);
$schema = ($this->type == 'quiz') ? WRITEPATH.'/Schemas/Quiz/questions.xsd': WRITEPATH.'/Schemas/Exam/questions.xsd';

$doc = new \DOMDocument;
$doc->load($this->file);
$result = $doc->schemaValidate($schema);
if(!$result) {
$this->setError(libxml_get_errors()[0]->message);

}


return $result;

}



}
