<?php

namespace App\Subscriptions;
use \App\Subscriptions\Transactions;

class Offers extends Transactions {
private $offerParams = ['name', 'description', 'code', 'discount', 'limit', 'product', 'target', 'expiry'];




public function check_code($code) {
$db = db_connect()->table('offers');
if($db->where('code', $code)->countAllResults() > 0)
return true;
return false;
}


public function new($data) {
if(!$this->check_required($this->offerParams, $data))
return false;

if($this->check_code($data['code']))
return $this->setError('This code is already taken. ');

$offers = db_connect()->table('offers');

$offers->insert($data);
return true;

}

public function all() {
$query = $this->db->table('offers')->where('expiry >', time())->get();
return $query->getResult();

}

public function getOffer($code) {
$offer = $this->db->table('offers')->where('code', $code)->get();
$offer = $offer->getRow();

if(!$offer)
return $this->setError(' Unable to find the offer. ');
return $offer;
}

public function notRedeemed($user_id, $code) {
return $this->db->table('redeemed_coupons')->where('user_id', $user_id)->where('code', $code)->countAllResults() <= 0;
}

public function notExpired($code) {
$offer = $this->db->table('offers')->where('code', $code)->get();
$offer = $offer->getRow();


if(!$offer)
return $this->setError('Unable to find the offer. ');

if($offer->expiry > 0 && $offer->expiry < time())
return $this->setError(' The offer has expired. ');

if($offer->limit > 0 && $this->db->table('redeemed_coupons')->where('code', $code)->countAllResults() >= $offer->limit)
return $this->setError(' The limit has been exceeded. ');

return true;
}

public function isEligible($user_id, $code, $plan_id) {
$offer = $this->getOffer($code);
if(!$offer)
return false;



if(strpos($offer->target, '@') !== false) {
[$id] = explode('@', $offer->target);
if($id != $user_id)
return $this->setError(' This offer was provided to an other user. ');
return $id === $user_id;
}

if($offer->product != $plan_id)
return $this->setError(' This offer is not applicable to this plan. ');

return true;

}

public function validateRedeem($transaction_id) {
$transaction = $this->getTransaction($transaction_id);

$offer = $this->getOffer($transaction->coupon);
if(!$offer)
return $this->setError(" Unable to find the particular offer. ");

$plan = $this->getPlan($transaction->plan_id);


if(!$plan)
return $this->setError(' Unable to find the plan/addon. ');

return true;
}


public function redeem($transaction_id) {
if(!$this->validateRedeem($transaction_id))
return false;

$transaction = $this->getTransaction($transaction_id);

if(!$this->check_code($transaction->coupon))
return $this->setError(' Unable to find the promo code. ');

if(!$this->notExpired($transaction->coupon))
return false;

if(!$this->notRedeemed($transaction->user_id, $transaction->coupon))
return $this->setError(' The code has been redeemed before. ');

$data = [
'user_id' => $transaction->user_id,
'transaction_id' => $transaction_id,
'code' => $transaction->coupon
];


$this->db->table('redeemed_coupons')->insert($data);
return true;
}

public function reprepare($transaction_id) {

$transaction = $this->getTransaction($transaction_id);
$user = $this->users->where('id', $transaction->user_id)->first();
$plan = $this->getPlan($transaction->plan_id);


$data = [
'txnid' => $transaction->transaction_id,
'amount' => $transaction->amount,
'productinfo' => $plan->description,
'firstname' => $user->first_name,
'lastname' => $user->last_name,
'email' => $user->email,
'phone' => $user->mobile,
'udf1' => $user->id,
'udf2' => $plan->id,
'key' => $this->key,
'surl' => $this->success,
'furl' => $this->failure,
];

if(!$this->check_required($this->offerParams, $data))
return false;

$data['hash'] = $this->getHash($data);
return $data;

}

public function getPromoCode() {
helper('text');
$num = 0;
$db = db_connect();
while(true) {
$code = random_string('alnum', $num < 10000 ? 6: 8);
if($db->table('offers')->where('code', $code)->countAllResults() == 0)
return $code;
}

}

public function getOffers($user_id) {
$db = db_connect();
$query = $db->table('offers')->where('expiry >', time())->where('target', 'all')->orWhere('target', $user_id.'@users')->get();
$result = $query->getResult();
return $result;
}



}
