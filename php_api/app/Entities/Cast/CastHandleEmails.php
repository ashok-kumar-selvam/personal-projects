<?php
namespace App\Entities\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

class CastHandleEmails extends BaseCast 
{

public static function set($value, $params = []) {
if($value != "")
return json_encode(explode(',', $value));
return json_encode([]);
}

public static function get($value, $params = []) {
return json_decode($value);
}

}
