<?php

namespace App\Commands\Plans;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Add extends BaseCommand
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
    protected $name = 'plans:add';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'command to add plans to admins.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'plans:add [whome, which plan] [options]';

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
$credits = new \App\Credits\Credits;
$monthlyPlans = $credits->monthlyPlans;
$planList = array_keys($monthlyPlans);

$plans = new \App\Models\Plans;
$users = new \App\Models\Users;
$email = (!isset($params[0])) ? CLI::prompt('To whome do you want to change or provide the plan? Please type "all" if you want to provide it to all'): $params[0];

if($email != 'all' && !$users->where('email', $email)->where('type', 'admin')->where('status', 'approved')->first())
return CLI::error(' Unable to find the admin with the given email id. ');

$plan = (!isset($params[1])) ? $planList[CLI::promptByKey('Which plan would you like to activate to this admin?', $planList)]: $params[1];


if(!isset($monthlyPlans[$plan]))
return CLI::error('Unable to find the specified plan. ');

switch($email) {
case "all":
$admins = $users->where('type', 'admin')->where('status', 'approved')->findAll();
$total = count($admins);
if(count($admins) <= 0)
return CLI::error(' There are no admins. ');
$changed = 0;
foreach($admins as $admin) {
$activePlan = $plans->getActivePlan($admin->id);
if($plan == $activePlan)
continue;

$plans->insert([
'user_id' => $admin->id,
'plan' => $plan,
'started_on' => time(),
'expires_on' => time()+(86400*365),
'status' => 'active'
]);
$changed++;
}

return CLI::write("$changed out of $total admins were activated $plan plan. ");
break;
default:
$user = $users->where('email', $email)->first();

if(!$email)
return CLI::error('Unable to find the user. ');

$activePlan = $plans->getActivePlan($user->id);

if($activePlan == $plan)
return CLI::error($user->first_name." has already subscribed to the $plan plan. ");

$plans->insert([
'user_id' => $user->id,
'plan' => $plan,
'started_on' => time(),
'expires_on' => time()+(86400*365),
'status' => 'active'
]);

return CLI::write($user->first_name." was activated $plan plan. ");

}

return CLI::write('everything is fine');
    }
}
