<?php
namespace App\Subscriptions;

use \App\Subscriptions\Subscriptions;

class Credits extends Subscriptions {

public function getAvailable($user_id) {
$user = $this->users->where('id', $user_id)->first();
if(!$user)
return $this->setError(' Unable to find the user. ');

if($user->type != 'admin')
return $this->setError(' The user is not an admin. ');

$total_credits = $spent_credits = 0;
$monthlyEarned = $this->ce->where('user_id', $user_id)->where('from <=', time())->where('to >=', time())->findAll();
foreach($monthlyEarned as $earned) {
$monthlySpent = $this->cs->where('credit_id', $earned->id)->countAllResults();
$spent_credits += $monthlySpent;
$total_credits += $earned->credit;
}
return $total_credits-$spent_credits;
}

public function getCreditId($user_id) {
$user = $this->users->where('id', $user_id)->first();

if(!$user)
return $this->setError(' Unable to find the user. ');

if($user->type != 'admin')
return $this->setError(' The user is not an admin. ');

$monthlyEarned = $this->ce->where('user_id', $user_id)->where('from <=', time())->where('to >=', time())->orderBy('priority', 'asc')->findAll();
foreach($monthlyEarned as $earned) {
if($this->cs->where('credit_id', $earned->id)->countAllResults() < $earned->credit)
return $earned->id;

}
return false;
}

public function setCredits(array $array) {
if(!isset($array['user_id']))
return $this->setError(' The user_id field is required. ');

if(!isset($array['mode']))
return $this->setError(' The mode parameter is required. ');

if($array['mode'] != 'renewal' && !isset($array['plan_id']))
return $this->setError(' The plan should be specified. ');




$user = $this->users->where('id', $array['user_id'])->first();
if(!$user)
return $this->setError(' Unable to find the user. ');

if($user->type != 'admin')
return $this->setError(' The user is not an admin. ');


switch($array['mode']) {
case "monthly":
$plan = $this->plans->where('id', $array['plan_id'])->first();
if(!$plan)
return $this->setError(' Unable to find the plan with the plan id. ');

if($plan->expires_on < time())
return $this->setError(' The plan is expired. ');


$plan = $this->getPremium($plan->plan);
if(!$plan)
return $this->setError(' Unable to find the plan in the plan list. ');
$this->ce->insert([
'user_id' => $array['user_id'],
'plan_id' => $array['plan_id'],
'credit' => $plan['credits'],
'from' => time(),
'to' => strtotime('next month'),
'category' => 'monthly',
'priority' => 1,
]);
return true;
break;
case "addon":
$addon = $this->getAddon($array['plan_id']);
if(!$addon)
return $this->setError(' Unable to find the addon plan with the provided id. ');

$this->ce->insert([
'user_id' => $array['user_id'],
'plan_id' => $addon['id'],
'credit' => $addon['credits'],
'from' => time(),
'to' => time()+(86400*$addon['validity']),
'category' => 'addon',
'priority' => 5,

]);
return true;
break;
case "renewal":
$active = $this->ce->where('user_id', $array['user_id'])->where('category', 'monthly')->where('from <', time())->where('to >', time())->first();
if(!$active) {
$plan  = $this->getActive($array['user_id']);

if(!$plan)
return $this->setError(' There is no active plan for this user. Please add a plan and try again. ');


return $this->setCredits(['user_id' => $array['user_id'], 'plan_id' => $plan->id, 'mode' => 'monthly']);

} else {
return $this->setError(' The credits did not expire yet. ');

}


break;
default:
return $this->setError(' Unknown mode. ');

}


}

public function fixCredits() {
$admins = $this->users->where('type', 'admin')->findAll();
foreach($admins as $admin) {
$this->setCredits($admin->id, 'renewal');

}
return true;
}



}
