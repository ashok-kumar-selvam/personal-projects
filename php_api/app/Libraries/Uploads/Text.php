<?php

namespace App\Libraries\Uploads;
use \App\Libraries\Uploads\BaseValidate;
use Yosymfony\Toml\Toml;

class Text extends BaseValidate
{


 public function validate() {
$text = $this->read_docx($this->file);
//$text = str_replace("“", "\"", $text);
//$text = str_replace("”", "\"", $text);
$text = remove_invisible_characters($text);

try {
$array = Toml::parse($text);
} catch(\Exception $e) {

return $this->setError($e->getMessage());
}

$encoded = $this->arrayToJson($array);
$json = new \App\Libraries\Uploads\JSON($encoded, $this->type);


$result = $json->validate();
if(!$result) {
$this->setError($json->getError());
} else {
$this->validated = true;
$this->setQuestions(json_decode($encoded));
}

return $result;

}


public function read_docx($filename){


$striped_content = '';
$content = '';

if(!$filename || !file_exists($filename)) {
$this->setError("The file does not exists");
return false;
}
    $zip = zip_open($filename);
if (!$zip || is_numeric($zip)) return false;

while ($zip_entry = zip_read($zip)) {

if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

if (zip_entry_name($zip_entry) != "word/document.xml") continue;

$content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

zip_entry_close($zip_entry);
    }
    zip_close($zip);      
    $content = str_replace('</w:r></w:p></w:tc><w:tc>', "\r\n", $content);
$content = str_replace('</w:r></w:p>', "\r\n", $content);
$striped_content = strip_tags($content);

return $striped_content;

}
}
