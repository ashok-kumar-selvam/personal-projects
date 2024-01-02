<?php

namespace App\Commands\Credits;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Get extends BaseCommand
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
    protected $name = 'credits:get';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = '';

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

$credits = new \App\Subscriptions\Credits;
$users = new \App\Models\Users;
$email = !isset($params[0]) ? CLI::prompt('Email of the user'): $params[0];
$user = $users->where('email', $email)->first();
if($email != 'all' && !$user)
return CLI::error('Unable to find the user. ');

if($email == 'all') {
$messages = [];

$admins = $users->where('type', 'admin')->findAll();
foreach($admins as $admin) 
$messages[$admin->first_name] = $credits->getAvailable($admin->id);
return CLI::write(json_encode($messages));
}


$remaining = $credits->getAvailable($user->id);


if(!$remaining)
return CLI::error('This user has no credits. ');

return CLI::write("There are $remaining credits with this user. ");
    }
}
