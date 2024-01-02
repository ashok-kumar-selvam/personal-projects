<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Answers extends Entity
{
    protected $datamap = [];

    protected $casts   = [
'question' => 'json',
'options' => 'json',
'chosen' => 'json',
'answer' => 'json'];

}
