<?php

namespace App\Libraries\Uploads;
use \App\Libraries\Uploads\BaseValidate;
use \App\Libraries\Uploads\JSON;
use Yosymfony\Toml\Toml;

class Txt extends BaseValidate 
{

public function validate() {
$text = file_get_contents($this->file);
$array = Toml::parse($text);
$encoded = $this->arrayToJson($array);
$json = new JSON($encoded, $this->type);
$result = $json->validate();
if(!$result) {
$this->setError($json->getError());
} else {
$this->validated = true;
$this->setQuestions(json_decode($encoded));
}

return $result;
}


}
