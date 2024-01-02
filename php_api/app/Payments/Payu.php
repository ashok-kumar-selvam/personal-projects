<?php
namespace App\Payments;
use \App\Payments\BasicPayments;

class Payu extends BasicPayments
{
private $key;
private $salt;
private $success;
private $failure;
protected $required = [
'key' => null,
'txnid' => null,
'amount' => null,
'productinfo' => null,
'firstname' => null,
'lastname' => null,
'email' => null,
'phone' => null,
'surl' => null,
'furl' => null,
//'hash' => null,
];


public $input_data = [];

public function __construct() {
$this->key = getEnv('ci_environment') == 'development' ? 'IE2BmP': 'K3dfSTqK';
$this->salt = getEnv('ci_environment') == 'development' ? 'Xmu7tCtmxXyLC0JYLz3LTKq09wzIXDFm': '9UjPxAVWdc';
$this->url = getEnv('ci_environment') == 'development' ? 'http://test.payu.in/_payment': 'https://secure.payu.in/_payment';
$this->success = getEnv('ci_environment') == 'development' ? getEnv('dev_api_url').'/payments/success': getEnv('api_url').'/payments/success';
$this->failure = getEnv('ci_environment') == 'development' ? getEnv('dev_api_url').'/payments/failure': getEnv('api_url').'/payments/failure';

$this->input_data['key'] = $this->key;
}

public function pay($data) {

$data['txnid'] = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
$data['key'] = $this->key;
$data['surl'] = $this->success;
$data['furl'] = $this->failure;

$this->input_data = $data;
if(!$this->check_required())
return false;
$hashRequired = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
$hashRequiredArray = explode('|', $hashRequired);
$hashString = '';
foreach($hashRequiredArray as $key) {
$hashString .= isset($data[$key]) ? $data[$key]: '';
$hashString .= '|';
}
$hashString .= $this->salt;
$data['hash'] = strtolower(hash('sha512', $hashString));

return [
'data' => $data,
'url' => $this->url];


return $client->post($this->url, ['body' => $data]);

}


}

