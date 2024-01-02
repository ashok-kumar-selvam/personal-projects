<?php

namespace App\Commands\Plans;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Get extends BaseCommand
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
    protected $name = 'plans:get';

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
$users = new \App\Models\Users;
$plans = new \App\Models\Plans;
$email = !isset($params[0]) ? CLI::prompt('Email of the user '): $params[0];
$user = $users->where('email', $email)->first();
if(!$user)
return CLI::error(' Unable to find the user. ');
$activePlan = $plans->getActivePlan($user->id);
if(!$activePlan)
return CLI::error($user->first_name." has noactive plans. ");


return CLI::write("$activePlan plan is active for ".$user->first_name);
    }
}
