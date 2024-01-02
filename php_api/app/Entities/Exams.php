<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Exams extends Entity
{
    protected $datamap = [];

    protected $casts   = [
'questions' => 'json-array'];
}
