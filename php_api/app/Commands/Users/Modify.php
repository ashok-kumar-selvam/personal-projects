<?php

namespace App\Commands\Users;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Modify extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'users';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'users:modify';

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
$email = isset($params[0]) ? $params[0]: CLI::prompt('Email of the user ');
$userQ = $db->table('users')->select('first_name, last_name, email, mobile, status,')->where('email', $email)->get();
$user = $userQ->getRow();
if(!$user)
return CLI::error("Unable to find the user with the email id $email . ");

$field = CLI::promptByKey('Please select what do you want to modify. ', $userQ->getRowArray());
$options = ['approved', 'pending', 'suspended'];

$value = CLI::promptByKey('Please select the status', $options);
$value = $options[$value];
$db->table('users')->where('email', $user->email)->set($field, $value)->update();
return CLI::write("The $field was changed to $value successfully. ");
    }
}
