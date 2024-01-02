<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Results extends Entity
{
    protected $datamap = [];
protected $dates = ['created_at', 'updated_at'];
protected $castHandlers = [
'ConvertTime' => '\App\Entities\Cast\CastConvertTime'];
    protected $casts   = [
'answers' => 'json',
'setting' => 'json',
'exam' => 'json',
'started_on' => 'ConvertTime',
'completed_on' => 'ConvertTime'
];
}
