<?php

namespace App\Securities;


class BaseSecurity 
{
    private $error;


public function __construct() {
$this->response = \Config\Services::response();
$this->request = service('request');
$this->db = \Config\Database::connect();
$this->plans = new \App\Models\Plans;
}

public function setError(string $error) {
$this->error = $error;
return false;
}

public function getError() {
return $this->error;
}


}
