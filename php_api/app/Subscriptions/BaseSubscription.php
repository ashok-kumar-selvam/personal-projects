<?php

namespace App\Subscriptions;

class BaseSubscription {
private $error;
public $discountLimit = 100;
private $premium = [
[
'id' => 'basic',
'name' => 'Basic free plan',
'description' => 'You can create unlimitted exams and receive 151 responses each month.',
'price' => 0,
'credits' => 151,
'features' => ['Unlimited quizzes with responses', 'Unlimited exams and 151 responses', 'Bulk question upload word, txt', 'Unlimited members', '2 groups', 'Unlimited questions', 'more'],
'total_price' => 0,
'validity' => '1 year',
],
[
'id' => 'silver',
'name' => 'Silver plan ',
'description' => 'Enjoy the most innovative features of this website and receive 251 responses/results each month. ',
'price' => 599,
'credits' => 251,
'features' => ['Unlimited exams with 251 responses ', 'Email notifications', '10 groups', 'Result analyse', 'Unlimited quizzes with responses', 'Bulk question upload word, txt', 'All features of free plan'],
'total_price' => 7188,
'validity' => '1 year',
],
[
'id' => 'gold',
'name' => 'Gold plan',
'description' => 'Most recommended plan to enjoy most features of this website. Get 500 responses with many intrusting features',
'price' => 999,
'credits' => 501,
'features' => ['50 groups', '2 instructor accounts', '501 exam responses', 'Automatic result publish and notification', '100 temporary members', 'all features of Silver plan'],
'total_price' => 11988,
'validity' => '1 year',
],

];

private $addons = [
[
'id' => 'addon101',
'credits' => 101,
'price' => 299,
'validity' => 365,
'subscribed' => true,
],
[
'id' => 'addon251',
'credits' => 251,
'price' => 699,
'validity' => 365,
'subscribed' => true,
],
[
'id' => 'addon501',
'credits' => 501,
'price' => 1299,
'validity' => 365,
'subscribed' => true,
],
[
'id' => 'addon801',
'credits' => 801,
'price' => 1919,
'validity' => 365,
'subscribed' => false,
],
[
'id' => 'addon1001',
'credits' => 1001,
'price' => 2199,
'validity' => 365,
'subscribed' => false,
],
];


public function __construct() {

$this->users = new \App\Models\Users;
$this->db = db_connect();
$this->ce = new \App\Models\CreditsEarned;
$this->cs = new \App\Models\CreditsSpent;
$this->plans = new \App\Models\Plans;
}

public function getPremiumPlans() {
return $this->premium;
}

public function getAddonPlans() {
return $this->addons;
}

public function getPremium($id) {
foreach($this->premium as $plan) {
if($plan['id'] == $id)
return $plan;

}

return false;
}

public function getAddon($id) {
foreach($this->addons as $addon) {
if($addon['id'] == $id)
return $addon;

}
return false;
}

public function getActive($user_id) {
$plan = $this->plans->where('user_id', $user_id)->where('started_on <=', time())->where('expires_on >=', time())->where('status', 'active')->orderBy('created_at', 'desc')->first();
if(!$plan)
return $this->setError(' Unable to find the active plan. ');
$plan = (array) $plan;
$plan = array_merge($this->getPremium($plan['plan']), $plan);

return (object) $plan;

}

public function setError($error) {
$this->error = $error;
return false;
}

public function getError() {
return $this->error;
}

public function cleanup() {
$plans = $this->plans->where('expires_on <', time())->findAll();
foreach($plans as $plan) {
$this->ce->where('user_id', $plan->user_id)->where('plan_id', $plan->id)->delete();
}
$this->ce->where('to <', time())->delete();

}

public function getProductNames() {
$data = [];
foreach($this->premium as $plan)
$data[$plan['id']] = $plan['name'];

foreach($this->addons as $addon)
$data[$addon['id']] = 'addon for '.$addon['credits'].' credits';

return $data;
}

public function getPlan($plan_id) {
$plans = $this->getProductNames();
if(!in_array($plan_id, array_keys($plans)))
return $this->setError(' Unable to find the plan with the plan id. ');
$plan = $this->getAddon($plan_id);
if(!$plan)
$plan = $this->getPremium($plan_id);

if(!$plan)
return $this->setError(' Unexpected error. Please contact the admin. ');
return (object) $plan;

}

public function check_required($required, $data) {

foreach($required as $key) {
if(!isset($data[$key]))
return $this->setError(" $key field is required. ");

}
return true;
}


}

