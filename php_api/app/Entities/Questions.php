<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Questions extends Entity
{
    protected $datamap = [];
    protected $dates   = [];
    protected $casts   = [
'question' => 'json',
'options' => 'json',
'answer' => 'json',
'settings' => 'json'];
}
