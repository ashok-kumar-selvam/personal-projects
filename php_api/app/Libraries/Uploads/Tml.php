<?php

namespace App\Libraries\Uploads;
use Yosymfony\Toml\Toml;
use \App\Libraries\Uploads\BaseValidate;
use \App\Libraries\Uploads\JSON;


class Tml extends BaseValidate
{



public function validate() {
$array = Toml::ParseFile($this->file);
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
