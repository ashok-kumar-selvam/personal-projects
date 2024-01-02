<?php

namespace App\Commands\Credits;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Add extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'credits';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'credits:add';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'add credits to all admins';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:name [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
$UserModal = new \App\Models\Users;
$credits = new \App\Subscriptions\Credits;

$email = !isset($params[0]) ? CLI::prompt('Email of the user '): $params[0];

if($email != 'all' && !$UserModal->where('email', $email)->where('type', 'admin')->where('status', 'approved')->first())
return CLI::error(' Unable to find the user in the admin list. ');

switch($email) {
case "all":

$users = $UserModal->where('status', 'approved')->where('type', 'admin')->findAll();

$results = [];
$added = $notAdded  = 0;
foreach($users as $user) {
if($user->type == 'admin') {
$result = $credits->setCredits(['user_id' => $user->id, 'mode' => 'renewal']);

if($result) {
$added++;
CLI::write("$user->email is given credit. ");
$results[$user->first_name] = 'success';
} else {
$notAdded++;

$results[$user->first_name] = $credits->getError();
}
}
}


return CLI::write(" $added admins were provided credits for this month and $notAdded users were already given credits.");

break;
default:
$user = $UserModal->where('email', $email)->first();

if(!$user)
return CLI::write(' Unable to find the user. ');
$result = $credits->setCredits(['user_id' => $user->id, 'mode' => 'renewal']);
if(!$result)
return CLI::error($credits->getError());

return CLI::write($user->first_name." was given this month credits successfully. ");
}
    }
}
