<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Instructors extends Entity
{
    protected $datamap = [];

    protected $casts   = [
'permissions' => 'json'
];
}
