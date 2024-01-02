<?php

namespace App\Commands\Plans;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Reset extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'plans';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'plans:reset';

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
$db = db_connect();
$email = isset($params[0]) ? $params[0]: CLI::prompt('Email of the user');
$userQ = $db->table('users')->where('email', $email)->get();
$user = $userQ->getRow();
if(!$user)
return CLI::error("Unable to find the user with the email id $email ");

$count = $db->table('plans')->where('user_id', $user->id)->countAllResults();
if($count <= 0)
return CLI::error('The user does not have any active plan.');

$confirm =CLI::prompt("Are you sure you want to reset the $count plans of ".$user->first_name."? Please type yes to continue. ");
if($confirm != 'yes')
return CLI::write('Operation canceled');

$db->table('plans')->where('user_id', $user->id)->delete();
return CLI::write('Operation successful.');


    }
}
