<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;
use \Instamojo\Instamojo;
class Payments extends ResourceController
{
protected $key = 'f8463c04a6b720472d44ea90a4e4b665'; //'test_ac4e147498322a189537e6ac71b';
protected $token = '69aa4bdf92a6eec23c01185edc4997c4'; //'test_8de73706bf57502d82e6dc3d5b6';
protected $salt = 'd2b4cef8e2824be1b4e74ef12a6e4097'; //'c3c22771d55b45ca9866628463f83e87';
protected $client_id = 'sJxexQxZ72o2USGfDzQbcYbkA1eYULlqV3gbmbfd'; //'test_92ZRi2yjl9dccCoRGLIbQpTX7LEfbn4xNGl';
protected $client_secret = 'bXeadp0c6Rk4wMCizrxG8Hd1v4rrV2av50SWqKHCjVE2B2iQ0PxVylU1nEW9BZ4U4qaXN4z7EzeR56bwiTAlPitgQWzkWZis0HB8Pjp8RxerSrHS7vOEbQBxt9IQK1Au'; //'test_pfY3DQzgurTUNf5c1HVSo7pFlGpRM8gYGTGXEVFChDjsZMxVqa5xPH4CrzoeAXDryuurBw1YyteDCuFLOLcf0vGGLY6lV4xjmB8sUIsUFQuM3BFgSDScfX1byi6';
protected $url = "https://instamojo.com/api/1.1/";
protected $products = [
['name' => 'credits_101', 'id' => 'credits_101', 'credits' => 101, 'price' => 249],
['name' => 'credits_251', 'id' => 'credits_251', 'credits' => 251, 'price' => 499],
['name' => 'credits_501', 'id' => 'credits_501', 'credits' => 501, 'price' => 799],
['name' => 'credits_701', 'id' => 'credits_701', 'credits' => 701, 'price' => 999],
['name' => 'credits_1001', 'id' => 'credits_1001', 'credits' => 1001, 'price' => 1199],
['name' => 'credits_1501', 'id' => 'credits_1501', 'credits' => 1501, 'price' => 1499],
['name' => 'credits_2001', 'id' => 'credits_2001', 'credits' => 2001, 'price' => 1699],
['name' => 'credits_2501', 'id' => 'credits_2501', 'credits' => 2501, 'price' => 1999],
['name' => 'credits_3001', 'id' => 'credits_3001', 'credits' => 3001, 'price' => 2249],
['name' => 'credits_5001', 'id' => 'credits_5001', 'credits' => 5001, 'price' => 2999],
];

public function __construct() {
$this->api = Instamojo::init('app', [
        "client_id" =>  $this->client_id, 
        "client_secret" => $this->client_secret    ]);

$this->users = new \App\Models\Users;
$this->credits = new \App\Credits\Credits;
$this->email = new \App\Controllers\Emails;
$this->payments = new \App\Models\Payments;
$this->plans = new \App\Models\Plans;
}

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
return $this->respond($this->products);
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {
try {
$response = $this->api->getPaymentRequestDetails($id);


return $this->respond([
'id' => $response['id'],
'purpose' => $response['purpose'],
'buyer_name' => $response['buyer_name'],
'email' => $response['email'],
'phone' => $response['phone'],
'status' => $response['status']]);
}
catch (Exception $e) {
return $this->fail(['message' => $e->getMessage()]);
}
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
$user = $this->users->where('id', $this->request->user->uuid)->first();
$product_id = $this->request->getVar('product_id');
if(!$product_id)
return $this->fail(['message' => 'Please provide the product id. You have given ']);

$product_list = array_column($this->products, 'id');
if(!in_array($product_id, $product_list))
return $this->fail(['message' => 'Invalid product id']);
$product = [];
foreach($this->products as $prod) {
if($prod['id'] == $product_id) {
$product = $prod;
break;
}

}

try {
$response = $this->api->createPaymentRequest([
'purpose' => $product['name'],
'amount' => $product['price'],
'buyer_name' => $user->first_name.' '.$user->last_name,
'email' => $user->email,
'phone' => $user->mobile,
'send_email' => false,
'redirect_url' => 'https://riyozo.com/admin/payments/success',
'webhook' => 'https://www.api.riyozo.com/admin/payments/webhook',
]);
return $this->respond([
'url' => $response['longurl']]);
}catch (Exception $e) {
 return $this->fail(['message' => $e->getMessage()]);
}
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return mixed
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
        //
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        //
    }


public function oldwebhook() {

helper('Credit');
$message = null;
$data = (array) $this->request->getVar();
$mac_provided = $data['mac'];  // Get the MAC from the POST data
unset($data['mac']);  // Remove the MAC key from the data.
$ver = explode('.', phpversion());
$major = (int) $ver[0];
$minor = (int) $ver[1];
if($major >= 5 and $minor >= 4){
     ksort($data, SORT_STRING | SORT_FLAG_CASE);
}
else{
     uksort($data, 'strcasecmp');
}

// You can get the 'salt' from Instamojo's developers page(make sure to log in first): https://www.instamojo.com/developers
// Pass the 'salt' without <>
$mac_calculated = hash_hmac("sha1", implode("|", $data), $this->salt);
if($mac_provided == $mac_calculated){
    if($data['status'] == "Credit"){
try {
$result = $this->credits->setCredits($data);
if(!$result)
sendTelegram(' There is some problem in set credits. '.$this->credits->getError());

if($result)
sendTelegram(' You have recieved payment from '.$data['buyer'].' and the credits are set successfully');
} catch(\Exception $e) {

sendTelegram(' There is an error in setting credits. '.$e->getMessage());
}

        // You can acess payment_request_id, purpose etc here. 
    }
    else{
sendTelegram(' the payment was not credited for '.$data['buyer']);
        // Payment was unsuccessful, mark it as failed in your database.
        // You can acess payment_request_id, purpose etc here.
    }
}
else{
sendTelegram("MAC mismatch");
}
return $this->respond('success');
}

public function payu_pay() {
$payu = new \App\Payments\Payu;
$result = $payu->pay();
if(!$result)
return $this->fail(['message' => $payu->getError()]);

return $this->respond($result);

}

public function success() {
$request = (array) $this->request->getVar();
$seqment = '/admin/payments/success?txnid='.$request['txnid'];
$url = getEnv('ci_environment') == 'development' ? getEnv('dev_app_url').$seqment: getEnv('app_url').$seqment;

return redirect()->to($url, null);
}

public function failure() {
$request = (array)  $this->request->getVar();
$seqment = '/admin/payments/failure?txnid='.$request['txnid'];
$url = getEnv('ci_environment') == 'development' ? getEnv('dev_app_url').$seqment: getEnv('app_url').$seqment;
return redirect()->to($url, null);
}

public function webhook() {
$request = (array) $this->request->getVar();

if(!isset($request['txnid']))
return $this->notify('A payment request is attempted without txnid.');

$transaction = $this->payments->where('transaction_id', $request['txnid'])->first();
if(!$transaction)
return $this->notify(' An invalid txnid is received. txnid: '.$request['txnid']);

if($transaction->status == 'success')
return $this->notify(' An existing succeeded transaction id is received. transaction_id: '.$request['txnid']);

$this->payments->where('transaction_id', $request['txnid'])->set('status', $request['status'])->update();

if($request['status'] != 'success')
return $this->notify('A failured request is received. transaction_id: '.$request['txnid']);


if($transaction->type == 'premium') {
$plans = new \App\Payments\Plans;
$monthly = $plans->getMonthly();
if(!isset($monthly[$transaction->plan])) 
return $this->notify(' unknown monthly plan is received. plan: '.$transaction->plan);


$plan = $monthly[$transaction->plan];
$this->plans->insert([
'user_id' => $transaction->user_id,
'plan' => $transaction->plan,
'started_on' => time(),
'expires_on' => time()+(86400*365),
'status' => 'active'
]);
} // end of transaction type if

$result = $this->credits->setCredits($request['txnid']);
if(!$result)
return $this->notify(' Unable to add credit. The error is '.$this->credits->getError());

return $this->notify('a transaction is completed successfully. transaction_id: '.$request['txnid'], 'success');
}

public function status() {
$request = (array) $this->request->getVar();
if(!isset($request['txnid']))
return $this->respond(['status' => 'failed']);

$transaction = $this->payments->where('transaction_id', $request['txnid'])->first();
if(!$transaction)
return $this->respond(['status' => 'failed']);

return $this->respond(['status' => $transaction->status]);
}

public function notify($message, $status = 'error') {
$this->email->toAdmin([
'subject' => $status == 'success' ? 'payment success': 'payment failed',
'message' => $message]);
return $this->respond('success');

}



}
