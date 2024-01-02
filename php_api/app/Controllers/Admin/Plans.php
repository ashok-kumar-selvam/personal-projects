<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;

class Plans extends ResourceController
{

public function __construct() {
$this->plans = new \App\Payments\Plans;
$this->users = new \App\Models\Users;
$this->payments = new \App\Models\Payments;
$this->credits = new \App\Credits\Credits;
$this->rplans = new \App\Models\Plans;
$this->transactions = new \App\Subscriptions\Transactions;
$this->url = getEnv('CI_ENVIRONMENT') == 'development' ? 'http://localhost:3000': 'https://riyozo.com';
$this->offers = new \App\Subscriptions\Offers;
$this->orders = new \App\Subscriptions\Orders;
}

public function getPremiumPlans() {

$data = $this->transactions->getPremiumPlans();
return $this->respond($data);
}


public function getPremium($id) {
$activePlan = $this->transactions->getActive($this->request->user->uuid);

if($id == 'basic' && $activePlan && $activePlan->id == 'basic')
return $this->fail(['message' => 'You are already subscribed to Basic plan. ']);

if($id == 'basic' && $this->transactions->isAvailable($this->request->user->uuid, 'basic'))
return $this->fail(['message' => 'You are already subscribed to Basic plan. ']);


if($id == 'basic') {

if(!$this->transactions->subscribe($this->request->user->uuid, $id))
return $this->fail(['message' => $this->transactions->getError()]);

// not needed because the user may try to change plan and gain credits 
//if(!$this->transactions->setCredits(['user_id' => $user->id, 'mode' => 'renewal']))
//return $this->fail(['message' => $this->transactions->getError()]);

return $this->respond([
'txnid' => 'success'
]);

}
$data = $this->orders->initiate($this->request->user->uuid, $id);
if(!$data)
return $this->fail(['message' => $this->orders->getError()]);

return $this->respond($data);
}

public function webhook() {

$request = (array) $this->request->getVar();

if(!$this->orders->verify($request))
return $this->notify($this->orders->getError());

if(!$this->orders->confirm($request))
return $this->notify($this->orders->getError());

$transaction = $this->orders->getTransaction($request['txnid']);

if(!is_null($transaction->coupon))
if(!$this->orders->redeem($request['txnid']))
return $this->notify($this->orders->getError());

$plan_id = $this->orders->subscribe($transaction->user_id, $transaction->plan_id, true);
if(!$plan_id)
return $this->notify($this->orders->getError());



if(!$this->orders->setCredits(['user_id' => $transaction->user_id, 'mode' => 'monthly', 'plan_id' => $plan_id]))
return $this->notify($this->orders->getError());
return $this->notify('A transaction is completed successfully. The transaction id is '.$transaction->transaction_id, 'success');

}


public function success() {
$request = (array) $this->request->getVar();
$seqment = '/admin/payments/success?txnid='.$request['txnid'];
$url = $this->url.$seqment;

return redirect()->to($url, null);
}

public function failure() {
$request = (array)  $this->request->getVar();
$seqment = '/admin/payments/failure?txnid='.$request['txnid'];
$url = $this->url.$seqment;
return redirect()->to($url, null);
}


public function status() {
$request = (array) $this->request->getVar();
if(!isset($request['txnid']))
return $this->respond(['status' => 'failed']);

$transaction = $this->orders->getTransaction($request['txnid']);
if(!$transaction)
return $this->respond(['status' => 'failed']);

return $this->respond(['status' => $transaction->status]);
}


public function notify($message, $status = 'error') {

$emails = new \App\Controllers\Emails;
$emails->toAdmin([
'subject' => $status == 'success' ? 'payment success': 'payment failed',
'message' => $message]);
return $this->respond('success');

}

public function applyCoupon() {
$rules = [
'coupon' => 'required',
'plan_id' => 'required',
];

$errors = [

];


if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());


$request = (array) $this->request->getVar();
$offer = $this->orders->applyCoupon($request['plan_id'], $request['coupon'], $this->request->user->uuid);

if(!$offer)
return $this->fail(['message' => $this->orders->getError()]);

return $this->respond($offer);
}

public function checkout() {
$rules = [
'user' => 'required',
'plan_id' => 'required',
];

$errors = [

];

if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();
$data = $this->orders->checkout($request);
if(!$data)
return $this->fail(['message' => $this->orders->getError()]);
return $this->respond($data);

}

}
