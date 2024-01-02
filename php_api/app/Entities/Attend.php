<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Attend extends Entity
{
    protected $datamap = [];
    protected $dates   = ['last_active'];
    protected $casts   = [];
}
