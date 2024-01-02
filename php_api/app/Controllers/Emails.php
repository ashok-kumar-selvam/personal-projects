<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use \Swift_SmtpTransport;
use \Swift_Mailer;
use \Swift_Message;
class Emails extends BaseController
{
protected $app;

public function __construct() {
$this->emails = new \App\Models\Emails;
$this->app  = [
'app_name' => 'Riyozo',
'app_url' => env('CI_ENVIRONMENT') == 'development' ? 'http://localhost:3000': APP_URL, 
'app_organization' => APP_NAME,
'app_email' => 'support@riyozo.com',
'app_feedback' => 'support@riyozo.com',
'app_unsubscribe' => 'https://riyozo.com/unsubscribe',
'app_description' => "Riyozo is an advance examination system build for teachers, instructors, schools, educational institutes, and everyone who wants to conduct exams.",
'file' => 'general.php',

];


}


	public function index()
	{
		//
	}


public function send($data, $smtp = false, $priority = 3) {
return false;
if(!isset($data['email']))
throw new \Exception(' Please provide an email to send an email. ');

if(!isset($data['subject']))
throw new \Exception(' Please provide the subject of the message. ');

if(!isset($data['message']))
throw new \Exception(' Please provide a message to send email. ');

$file = isset($data['file']) ? $data['file']: $this->app['file'];

$email = \Config\Services::email();

$config = [
'protocol' => 'smtp',
'SMTPHost' => 'mail.riyozo.com', //'email-smtp.ap-south-1.amazonaws.com',
'SMTPUser' => 'support@riyozo.com', //'AKIAUVQPSHW5J6BCSRW3',
'SMTPPass' => 'Vinayaga@111', //'BEU57R0uxsjzhn7b1adzDMy1IOeQ3I3ZVRVm0HP85MOw',
'SMTPPort' => 587,
'SMTPCrypto' => 'tls',
'priority' => 1,

];

$email->initialize($config);

$email->setFrom($this->app['app_email'], $this->app['app_name']);
$email->setTo($data['email']);
$email->setSubject($data['subject']);
$email->setMessage(view('emails/'.$file, array_merge($data, $this->app)));

$result = $email->send();

return $result;
}

public function smtp($data) {
$config = [
'protocol' => 'smtp',
'SMTPHost' => '88.99.242.20',
'SMTPUser' => 'support@tamiltutor.in',
'SMTPPass' => 'Vinayaga@111',
'SMTPPort' => 465,
'priority' => 1,
];

$email->initialize($config);

}


public function ssend($to_mail, $to_name, $subject, $msg, $from_mail = "support@tamiltutor.in", $from_name = "tamil tutor") {
//if(getEnv('CI_ENVIRONMENT') == 'production') {
$to_mail = 'asokmadurai@gmail.com';
if(true) {
try {
$transport = (new Swift_SmtpTransport('88.99.242.20', 25))->setUsername('support@tamiltutor.in')->setPassword('Vinayaga@111');
$mailer = new Swift_Mailer($transport);
$message = new Swift_Message();
$message->setSubject($subject);
$message->setFrom([$from_mail => $from_name]);
$message->addTo($to_mail, $to_name); /*
    // Add "CC" address [Use setCc method for multiple recipients, argument should be array]
//    $message->addCc('recipient@gmail.com', 'recipient name');
    // Add "BCC" address [Use setBcc method for multiple recipients, argument should be array]

//    $message->addBcc('recipient@gmail.com', 'recipient name');
    // Add an "Attachment" (Also, the dynamic data can be attached)
//    $attachment = Swift_Attachment::fromPath('example.xls');
//    $attachment->setFilename('report.xls');
//    $message->attach($attachment);
    // Add inline "Image"
//    $inline_attachment = Swift_Image::fromPath('nature.jpg');

//    $cid = $message->embed($inline_attachment);
    // Set the plain-text "Body"
//    $message->setBody("This is the plain text body of the message.\nThanks,\nAdmin");
    // Set a "Body" */



$message->addPart($msg, 'text/html');

return $mailer->send($message);
} catch(Exception $e) {
return false;
}

} else {
return false;
}


}

public function report($data) {
$email = 'asokmadurai@gmail.com';
$name = 'ashokkumar';
$message = view('emails/error_occured', $data);

return $this->send($email, $name, "error occured", $message);

}

public function sendinblue($data, $smtp, $priority) {
if(!$this->isValid($data))
return false;

$file = isset($data['file']) ? $data['file']: $this->app['file'];
if(getEnv('CI_ENVIRONMENT') == 'development') {
$this->emails->insert([
'from' => 'website',
'to' => $data['email'],
'subject' => $data['subject'],
'message' => view('emails/'.$file, array_merge($data, $this->app)),
]);
return true;
}

$email = \Config\Services::email();

$config = [
'protocol' => 'smtp',
'SMTPHost' => 'smtp-relay.sendinblue.com', //'smtppro.zoho.in', 
'SMTPUser' => 'vendruvidu@gmail.com', //'AKIAUVQPSHW5J6BCSRW3', //'support@riyozo.com', 
'SMTPPass' => 'QzB8TbxK254v3XjZ', //'BEU57R0uxsjzhn7b1adzDMy1IOeQ3I3ZVRVm0HP85MOw', //'Vinayaga@sprt1', 
'SMTPPort' => 587,
'SMTPCrypto' => 'tls',
'priority' => 1,

];

$email->initialize($config);

$email->setFrom($this->app['app_email'], $this->app['app_name']);
$email->setTo($data['email']);
$email->setSubject($data['subject']);
$email->setMessage(view('emails/'.$file, array_merge($data, $this->app)));

$result = $email->send();

return $result;
}

protected function isValid($data) {
if(!isset($data['email'])) {

throw new \Exception(' Please provide an email to send an email. ');
return false;
}

if(!isset($data['subject'])) {
throw new \Exception(' Please provide the subject of the message. ');
return false;
}

if(!isset($data['message'])) {

throw new \Exception(' Please provide a message to send email. ');
return false;
}
return true;

}

public function toAdmin($data) {
$email = \Config\Services::email();

$config = [
'protocol' => 'smtp',
'SMTPHost' => 'mail.riyozo.com', //'email-smtp.ap-south-1.amazonaws.com',
'SMTPUser' => 'support@riyozo.com', //'AKIAUVQPSHW5J6BCSRW3',
'SMTPPass' => 'Vinayaga@111', //'BEU57R0uxsjzhn7b1adzDMy1IOeQ3I3ZVRVm0HP85MOw',
'SMTPPort' => 587,
'SMTPCrypto' => 'tls',
'priority' => 1,

];

$email->initialize($config);

$email->setFrom($this->app['app_email'], $this->app['app_name']);
$email->setTo('admin@riyozo.com');
$email->setSubject($data['subject']);
$email->setMessage(view('emails/'.$this->app['file'], array_merge($data, $this->app)));

$result = $email->send();

return $result;

}



}
