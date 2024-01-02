<?php

namespace App\Subscriptions;
use \App\Subscriptions\Credits;
class Transactions extends Credits {

private $key;
private $salt;
private $success;
private $failure;
private $url;
private $transactionParams = ['key', 'txnid', 'amount', 'productinfo', 'firstname', 'lastname', 'email', 'phone', 'udf1', 'udf2', 'surl', 'furl',];
private $hashes = ["key", "txnid", "amount", "productinfo", "firstname", "email", "udf1", "udf2", "udf3", "udf4", "udf5", "udf6", "udf7", "udf8", "udf9", "udf10"];

public function __construct() {
parent::__construct();
$this->key = getEnv('ci_environment') == 'development' ? 'IE2BmP': 'K3dfSTqK';
$this->salt = getEnv('ci_environment') == 'development' ? 'Xmu7tCtmxXyLC0JYLz3LTKq09wzIXDFm': '9UjPxAVWdc';
$this->url = getEnv('ci_environment') == 'development' ? 'http://test.payu.in/_payment': 'https://secure.payu.in/_payment';
//$this->url = 'https://sandboxsecure.payu.in/_payment';
$this->success = getEnv('ci_environment') == 'development' ? 'http://localhost:8080/payments/success': 'https://www.api.riyozo.com/payments/success';
$this->failure = getEnv('ci_environment') == 'development' ? 'http://localhost:8080/payments/failure': 'https://www.api.riyozo.com/payments/failure';
$this->payments = new \App\Models\Payments;
}



public function getHash(array $data) {
$hashString = '';
foreach($this->hashes as $key) {
$hashString .= isset($data[$key]) ? $data[$key]: '';
$hashString .= '|';
}
$hashString .= $this->salt;
return strtolower(hash('sha512', $hashString));
}

public function prepareTransaction($data) {
$data['txnid'] = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
$data['key'] = $this->key;
$data['surl'] = $this->success;
$data['furl'] = $this->failure;

if(!$this->check_required($this->transactionParams, $data))
return false;

$data['hash'] = $this->getHash($data);
$this->data = $data;

return [
'data' => $data,
'url' => $this->url,
];

}

public function initiateTransaction($data, $code = null) {


$this->payments->insert([
'transaction_id' => $data['txnid'],
'user_id' => $data['udf1'],
'plan_id' => $data['udf2'],
'amount' => $data['amount'],
'hash' => $data['hash'],
'coupon' => $code,
'status' => $data['udf2'] == 'basic' ? 'success': 'initiated',
]);

}

public function verify($data) {




return true;
}

public function confirm($data) {

if(!isset($data['txnid']))
return $this->setError(' Unable to find the txnid in the request. ');


$transaction = $this->getTransaction($data['txnid']);
if(!$transaction)
return $this->setError(' An invalid txnid is received. ');


if($transaction->status == 'success')
return $this->setError(' An existing succeeded transaction id is received. transaction_id: '.$data['txnid']);

$this->payments->where('transaction_id', $data['txnid'])->set('status', $data['status'])->update();

if($data['status'] != 'success')
return $this->setError('A failured request is received. transaction_id: '.$data['txnid']);

return true;
}

public function getTransaction($id) {
$transaction = $this->payments->where('transaction_id', $id)->first();
return $transaction;

}


}
