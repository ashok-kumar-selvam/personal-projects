<?php

namespace App\Controllers\Common;

use CodeIgniter\RESTful\ResourceController;

class Answers extends ResourceController
{

protected $modelName = 'App\Models\Answers';
protected $format = 'json';

public function __construct() {
$this->entity = new \App\Entities\Answers;
$this->db = \Config\Database::connect();
$this->attempts = new \App\Models\Attempts;

}

    public function show($id = null)
    {
return $this->respond($this->model->find($id));

    }

    public function create()
    {

$rules = [
'type' => 'required',
'number' => 'required',
'time' => 'required|greater_than_equal_to[0]',
'result_id' => 'required|is_not_unique[results.id]',
'question_id' => 'required|is_not_unique[questions.id]',
'question' => 'required',
'options' => 'required_if[type,single_choice,multi_choice,true_or_false]'];
$errors = [
'options' => [
'required_if' => 'You must provide the options for the answer. ']
];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();
if(!$request['answer'])
$request['answer'] = '';


$entity = new \App\Entities\Answers($request);

$id = $this->model->insert($entity);

return $this->respond([
'id' => $id,
'message' => 'success']);
    }


    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {

$rules = [
'id' => 'required|is_not_unique[answers.id]',
'type' => 'required',
'number' => 'required',
'time' => 'required|greater_than_equal_to[0]',
'total' => 'required|greater_than_equal_to[0]',
'has_attempted' => 'required|in_list[yes]',
'result_id' => 'required|is_not_unique[attempts.id]',
'question_id' => 'required|is_not_unique[questions.id]',
'question' => 'required',
'options' => 'required_if[type,single_choice,multi_choice,true_or_false]'];
$errors = [
'options' => [
'required_if' => 'You must provide the options for the answer. ']
];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());
$request = (array) $this->request->getVar();

if(isset($request['has_next']))
unset($request['has_next']);

if(isset($request['has_previous']))
unset($request['has_previous']);

if(isset($request['is_correct']))
unset($request['is_correct']);

$request['has_answered'] = 'yes';
if(!isset($request['answer']) || empty($request['answer']))
$request['has_answered'] = 'no';

$entity = new \App\Entities\Answers($request);
$this->model->save($entity);

$this->db->table('attend')->set('time', $request['total'])->where('result_id', $request['result_id'])->update();

switch($request['action']) {
case "next":
$question = $this->model->join('questions', 'questions.id = answers.question_id')->select('questions.point as given_point, answers.*')->where('answers.id', $request['id']+1)->where('result_id', $request['result_id'])->first();
if(!$question)
return $this->fail(['message' => 'Invalid request. There is no next question. ']);
$question->has_attempted = 'yes';
return $this->respond($question);

break;
case "previous":

$question = $this->model->join('questions', 'questions.id = answers.question_id')->select('questions.point as given_point, answers.*')->where('answers.id', $request['id']-1)->where('result_id', $request['result_id'])->first();
if(!$question)
return $this->fail(['message' => 'Invalid request. There is no previous question']);

$question->has_attempted = 'yes';
return $this->respond($question);
break;
case "finish":
return $this->respond('successfully saved');

break;
case "current":
$question = $this->model->join('questions', 'questions.id = answers.question_id')->select('questions.point as given_point, answers.*')->where('answers.id', $request['answer_id'])->first();
if(!$question)
return $this->failNotFound('Unable to find the question.');
$question->has_attempted = 'yes';
return $this->respond($question);
break;
case "empty":

return $this->respond('successfully saved');
break;
}

    }

protected function has_next($id, $result_id) {
$question = $this->model->where('id', $id+1)->where('result_id', $result_id)->first();
if(!$question)
return false;
return true;
}

public function marked($id) {
$questions = $this->model->select('question, id, number')->where('has_marked', 'yes')->where('result_id', $id)->orderBy('number', 'asc')->findAll();

$total = count($questions);

if($total%3 == 1) {
array_push($questions, ['number' => 0, 'id' => null, 'question' => 'empty']);
array_push($questions, ['number' => 0, 'id' => null, 'question' => 'empty']);
} else if($total%3 == 2) {
array_push($questions, ['number' => 0, 'id' => null, 'question' => 'empty']);
}


$qss = [];
for($i = 0; $i < count($questions)-1; $i = $i+3) {
$subArray = array_slice($questions, $i, 3);
array_push($qss, $subArray);

}


return $this->respond($qss);
}

public function all($id) {
$questions = $this->model->select('id, number, question')->where('result_id', $id)->findAll();
$total = count($questions);

if($total%3 == 1) {
array_push($questions, ['number' => 0, 'id' => null, 'question' => 'empty']);
array_push($questions, ['number' => 0, 'id' => null, 'question' => 'empty']);
} else if($total%3 == 2) {
array_push($questions, ['number' => 0, 'id' => null, 'question' => 'empty']);
}


$qss = [];
for($i = 0; $i < count($questions)-1; $i = $i+3) {
$subArray = array_slice($questions, $i, 3);
array_push($qss, $subArray);

}


return $this->respond($qss);
}



}
