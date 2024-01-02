<?php

namespace App\Commands\Users;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Clear extends BaseCommand
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
    protected $name = 'users:clear';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Clear the user account';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'users:clear [email] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = ['email' => 'The email of the user'];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = ['email' => 'the email'];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {

$users = new \App\Models\Users;
$environment = env('ci_environment');
CLI::prompt("Are you sure you want to remove all users from the $environment database?", ['yes', 'no']);
if($environment == 'production')
CLi::prompt("You are performing this action on the production database. This action cannot be undone. still do you want to continue? Please type delete production to continue.", ['delete production']);

$users->whereNotIn('id', ['abcd'])->delete();

return CLI::write('The user table is cleared! There are '.$users->countAllResults().' users remaining');
$users->where('email', $email)->delete();
return CLI::write('The user is deleted successfully');



    }
}
