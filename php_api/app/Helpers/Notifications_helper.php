<?php

function notify($user_id, $message) {
$db = db_connect();
$db->table('notifications')->insert([
'action' => 'created',
'member_id' => $user_id,
'message' => $message,

]);
return true;
}

