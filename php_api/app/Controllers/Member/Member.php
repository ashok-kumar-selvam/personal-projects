<?php

namespace App\Controllers\Member;

use CodeIgniter\RESTful\ResourceController;

class Member extends ResourceController
{
protected $format = 'json';

public function __construct() {
$this->notifications = new \App\Models\Notifications;
}

    public function updateNotification($id = null)
    {
$rules = [
'action' => 'required|in_list[read,dismissed]'];
if(!$this->validate($rules))
return $this->failValidations($this->validator->getErrors());
$request = (array) $this->request->getVar();

$this->notifications->update($id, ['action' => $request['action']]);
return $this->respond('success');
    }

}