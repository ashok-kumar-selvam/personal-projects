<?php

namespace App\Libraries\Notifications;

class BaseNotification {

public function __construct() {
$this->model = new \App\Models\Notifications;
$this->users = new \App\Models\Users;
}

}

