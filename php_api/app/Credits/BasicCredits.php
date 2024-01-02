<?php
namespace App\Credits;

class BasicCredits 
{
private $error;

public function __construct() {

$this->dates = [
'now' => time(),
'month_start' => strtotime(' first day of this month midnight'),
'month_end' => strtotime(' -1 second first day of next month midnight'),
'week_start' => strtotime(' this week midnight'),
'week_end' => strtotime(' -1 second next week midnight'),
'year_start' => strtotime(' first day of january this year midnight'),
'year_end' => strtotime(' -1 second first day of january next year midnight'),
'one_year' => strtotime(' -1 second next year'),
];

$planlist = new \App\Payments\Plans;
$this->monthlyPlans = $planlist->getMonthly();
$this->addonPlans = $planlist->getAddon();

$this->db = db_connect();
$this->transactions = new \App\Models\Payments;
$this->plans = new \App\Models\Plans;
$this->ce = new \App\Models\CreditsEarned;
$this->cs = new \App\Models\CreditsSpent;
$this->users = new \App\Models\Users;

}


public function setError($error) {
$this->error = $error;
return false;
}

public function getError() {
return $this->error;
}


}
