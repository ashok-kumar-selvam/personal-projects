<?php
namespace App\Credits;
use \App\Credits\BasicCredits;

class Credits extends BasicCredits {
public function getRemaining($user_id) {

$db = db_connect();
$start = $this->dates['month_start'];
$end = $this->dates['month_end'];

$total_query = $db->table('credits_earned')->where('user_id', $user_id)->where('from <=', $start)->where('to >=', $end)->selectSum('credit', 'total_credits')->get();
$total_result = $total_query->getRow();
$spent_credits = $db->table('credits_spent')->where('user_id', $user_id)->where('date >=', $start)->where('date <=', $end)->countAllResults();
return $total_result->total_credits-$spent_credits;
}

public function getCreditId($user_id) {
$totalQuery = $this->db->table('credits_earned')->where('from <', time())->where('to >', time())->where('user_id', $user_id)->orderBy('priority', 'asc')->get();
$totalResult = $totalQuery->getResult();
if(count($totalResult) <= 0) {
$this->setError('You have no credits');
return false;

}
$credit_id = false;
foreach($totalResult as $credit) {
if($this->db->table('credits_spent')->where('credit_id', $credit->id)->countAllResults() < $credit->credit) {
$credit_id = $credit->id;
break;
}

}
return $credit_id;
}

public function setCredits($transaction_id) {
$transaction = $this->transactions->where('transaction_id', $transaction_id)->first();

if(!$transaction)
return $this->setError('Invalid transaction id');

if($transaction->status != 'success')
return $this->setError('The transaction status  is not success. ');

if($transaction->type == 'premium' && !isset($this->monthlyPlans[$transaction->plan]))
return $this->setError(' Unable to find the premium plan. ');

if($transaction->type == 'addon' && !isset($this->addonPlans[$transaction->plan]))
return $this->setError('Unable to find the addon plan. ');

switch($transaction->type) {
case "premium":
$activePlan = $this->plans->getActivePlan($transaction->user_id);
//un necessary because the validation is done in the paymonthly function 
//if($transaction->plan == 'basic' && $activePlan)
//return $this->setError(' An active plan is already present for this account. transaction_id: '.$transaction->transaction_id);



$plan = $this->monthlyPlans[$transaction->plan];
$this->ce->insert([
'user_id' => $transaction->user_id,
'category' => 'monthly',
'priority' => 1,
'from' => $this->dates['month_start'],
'to' => $this->dates['month_end'],
'credit' => $plan['credits']
]);
return true;
break;
case "addon":
$plan = $this->addonPlans[$transaction->plan];
$this->ce->insert([
'user_id' => $transaction->user_id,
'category' => 'addon',
'priority' => 5,
'credit' => $plan['credits'],
'from' => time(),
'to' => time()+(86400*365),

]);
return true;
default:
return $this->setError('Invalid plan type. ');
}
return $this->setError('Unknown error. ');
}


public function setMonthlyCredits($user_id) {

$user = $this->users->where('id', $user_id)->first();
if(!$user)
return $this->setError('unknown user. ');


if($user->type != 'admin')
return $this->setError('Only admins will be allowed to get credits. ');

$activePlan = $this->plans->getActivePlan($user_id);
if(!$activePlan)
return $this->setError(' This user has no plans. ');

if(!isset($this->monthlyPlans[$activePlan]))
return $this->setError(' unable to find the particular plan in the list. ');

if(!$this->ce->where('from', $this->dates['month_start'])->where('to', $this->dates['month_end'])->where('category', 'monthly')->where('user_id', $user_id)->first()) {

$monthlyPlan = $this->monthlyPlans[$activePlan];
$this->ce->insert([
'user_id' => $user_id,
'from' => $this->dates['month_start'],
'to' => $this->dates['month_end'],
'credit' => $monthlyPlan['credits'],
'category' => 'monthly',
'priority' => 1
]);
return true;
}
return $this->setError(' already given. ');
}


}
