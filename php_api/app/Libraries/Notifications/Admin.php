<?php

namespace App\Libraries\Notifications;

use \App\Libraries\Notifications\BaseNotification;

class Admin extends BaseNotification 
{

public function notify($member_id, $message) {
$this->notifications->insert([
'to' => 'admin',
'member_id' => $member_id,
'message' => $message,
'action' => 'created',

]);
return true;
}


}

