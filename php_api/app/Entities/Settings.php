<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Settings extends Entity
{
    protected $datamap = [];
protected $castHandlers = [
'HandleEmails' => '\App\Entities\Cast\CastHandleEmails',
'ConvertTime' => '\App\Entities\Cast\CastConvertTime'];



    protected $casts   = ['start_time' => 'ConvertTime',
'instructions' => 'json',
'end_time' => 'ConvertTime',
'publish_on' => 'ConvertTime',
'allowed_emails' => 'HandleEmails'];
}
