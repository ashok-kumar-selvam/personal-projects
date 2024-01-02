<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class QuizResults extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at'];



    protected $casts   = [
'result' => 'json'];
}
