<?php

namespace App\Controllers\Common;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Files\File;

class Files extends ResourceController
{
protected $modelName = '\App\Models\Files';
protected $format = 'json';

private $aws_access_key = 'AKIAUVQPSHW5DM2FMYG2';
private $aws_secret_key = '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';

public function __construct() {
$this->s3 = new \Aws\S3\S3Client([
	'region'  => 'ap-south-1',
	'version' => 'latest',
'credentials' => [
    'key'    => $this->aws_access_key,
    'secret' => $this->aws_secret_key,
]
]);
}

    public function show($id = null)
    {
$file = $this->model->where('id', $id)->first();
if(!$file)
    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
$result = $this->s3->getObject([
'Bucket' => 'riyozoquestionfiles',
'Key' => $file->path]);

$response = \Config\Services::response();

return $this->response->download($file->name,  $result->get('Body'));

    }

    public function create()
    {
$rules = [
'question_id' => 'required|is_not_unique[questions.id]',
'result_id' => 'required|is_not_unique[attempts.id]',
'answer' => 'uploaded[answer]|max_size[answer, 1024]'];
if(!$this->validate($rules)) 
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();
$file = $this->request->getFile('answer');
if(!$file->isValid())
return $this->fail(['message' => $file->getErrorString()]);
try {
$filelocation = $file->getTempName();
$filename = $file->getRandomName();
$result = $this->aws_upload($filename, $filelocation);

} catch(\Exception $e) {
return $this->fail(['message' => ' some error in result '.$e->getMessage()]);
}


$name = $file->getName();
$us = new \App\Securities\UserSecurity;
$user = $us->getData();

$file_id = $this->model->insert([
'member_id' => $user->uuid,
'result_id' => $request['result_id'],
'question_id' => $request['question_id'],
'name' => $name,
'size' => $file->getSizeByUnit('kb'),
'path' => $filename,
'type' => $file->getClientExtension() ]);

return $this->respond([
'file_id' => $file_id]);
    }



public function aws_upload($filename, $filelocation) {
try {


// Send a PutObject request and get the result object.
$result = $this->s3->putObject([
	'Bucket' => 'riyozoquestionfiles',
	'Key'    => $filename,
	'SourceFile' => $filelocation,
]);

// Print the body of the result by indexing into the result object.
return $result;
} catch(\Exception $e) {
return 'error occured '.$e->getMessage();
}

}

}
