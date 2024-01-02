<?php
namespace App\Entities\Cast;
use CodeIgniter\Entity\Cast\BaseCast;

class CastConvertTime extends BaseCast {

public static function get($value, $params = []) {
if($value == 0)
return $value;
return $value*1000;
}

public static function set($value, $params = []) {
if($value == 0)
return $value;
return strtotime($value);
}


} 