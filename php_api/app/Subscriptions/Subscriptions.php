<?php

namespace App\Subscriptions;
use \App\Subscriptions\BaseSubscription;
class Subscriptions extends BaseSubscription {

public function subscribe($user_id, $plan_id, $expiration = false) {
$user = $this->users->where('id', $user_id)->first();

if(!$user)
return $this->setError('Unable to find the user. ');

if($user->type !== 'admin')
return $this->setError(' The user is not an admin. ');

$plan = $this->getPremium($plan_id);
if(!$plan)
return $this->setError(' Unable to find the plan. ');
$expiration = !$expiration ? 'next month': $plan['validity'];

$subscribe_id = $this->plans->insert([
'user_id' => $user_id,
'plan' => $plan['id'],
'started_on' => time(),
'expires_on' => strtotime($expiration),
'status' => 'active',
]);
$this->activate($subscribe_id);
return $subscribe_id;
}

public function unsubscribe($subscribe_id) {
return $this->plans->delete($subscribe_id);
}

public function activate($subscribe_id) {

$subscription = $this->plans->where('id', $subscribe_id)->first();

if(!$subscription)
return $this->setError(' Unable to find the subscription. ');

if($subscription->expires_on < time())
return $this->setError(' This subscription is expired. ');


$this->plans->where('user_id', $subscription->user_id)->set('status', 'inactive')->update();
$this->plans->where('id', $subscribe_id)->set('status', 'active')->update();

return true;

}

public function deactivate($subscribe_id) {
$subscription = $this->plans->where('id', $subscribe_id)->first();
if(!$subscription)
return $this->setError(' Unable to find the subscription. ');

$this->plans->where('id', $subscribe_id)->set('status', 'inactive')->update();

return true;
}

public function isAvailable($user_id, $plan) {
$plan = $this->plans->where('user_id', $user_id)->where('plan', $plan)->where('expires_on >', time())->first();
if(!$plan)
return false;
return true;
}



}

