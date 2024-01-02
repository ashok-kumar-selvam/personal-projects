<?php

namespace App\Subscriptions;
use \App\Subscriptions\Offers;

class Orders extends Offers {

public function initiate($user_id, $plan_id) {

$plan = $this->getPlan($plan_id);
if(!$plan)
return $this->setError(' Unable to find the plan. ');

$user = $this->users->where('id', $user_id)->first();
if(!$user)
return $this->setError(' The user is not found ');

if($user->type != 'admin')
return $this->setError('Invalid user. ');
$user = (object) [
'firstname' => $user->first_name,
'lastname' => $user->last_name,
'email' => $user->email,
'phone' => $user->mobile,
'udf1' => $user->id,
];

return [
'plan' => $plan,
'user' => $user,
];

}

public function applyCoupon($plan_id, $code, $user_id) {

$offer = $this->getOffer($code);
if(!$offer)
return $this->setError(' Invalid promo code. ');


$plan = $this->getPlan($plan_id);
if(!$plan)
return false; //$this->setError(' Unable to find the plan associated with this promo code. ');

if(!$this->check_code($code))
return $this->setError(' Unable to find the promo code. ');

if(!$this->notExpired($code))
return false;

if(!$this->notRedeemed($user_id, $code))
return $this->setError(' The code has been redeemed before. ');

if(!$this->isEligible($user_id, $code, $plan->id))
return false;

$amount = round($plan->total_price-(($plan->total_price/100)*$offer->discount));

return [
'price' => $plan->total_price,
'discount' => $offer->discount,
'saved' => $plan->total_price-$amount,
'amount' => $amount,
'code' => $code];


}

public function checkout($request) {
$data = [];
$user = (array) $request['user'];
$plan = $this->getPlan($request['plan_id']);
$code = null;
if(isset($request['code'])) {
$offer = $this->applyCoupon($plan->id, $request['code'], $user['udf1']);
if(!$offer)
return false;
$data['amount'] = $offer['amount'];
$code = $offer['code'];
} else {
$data['amount'] = $plan->total_price;

}

$data['productinfo'] = $plan->description;
$data['udf2'] = $plan->id;
$data = array_merge($data, $user);

$data = $this->prepareTransaction($data);
if(!$data)
return false;
$this->initiateTransaction($data['data'], $code);

return $data;

}



}

