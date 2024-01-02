<?php

namespace App\Commands\Users;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Remove extends BaseCommand
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
    protected $name = 'users:remove';

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
$user = $db->table('users')->where('email', $email)->get();
$user = $user->getRow();
if(!$user)
return CLI::error(" Unable to find the user with email id $email ");

if($user->type == 'member') {
$db->table('users')->where('id', $user->id)->delete();
return CLI::write(' The user has been deleted successfully. ');
}



$adminTables = ['exams', 'quizzes', 'groups', 'user_members', 'questions', 'plans', 'credits_earned', 'credits_spent'];
foreach($adminTables as $table)
$db->table($table)->where('user_id', $user->id)->delete();
$db->table('users')->where('id', $user->id)->delete();
return CLI::write($user->first_name.' was successfully removed');


    }
}
