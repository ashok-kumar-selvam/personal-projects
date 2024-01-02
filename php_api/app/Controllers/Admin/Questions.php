<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;

class Questions extends ResourceController
{
protected $modelName = '\App\Models\Questions';
protected $format = 'json';
public function __construct() {

$this->exams = new \App\Models\Exams;
$this->quizzes = new \App\Models\Quizzes;
$this->questionsheet = new \App\Models\QuestionSheet;
$this->us = new \App\Securities\UserSecurity;
$this->is = new \App\Securities\InstructorSecurity;
}

    public function bank($exam_id)
    {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('questions', 'view'))
return $this->fail(['message' => $this->is->getError()]);
$user_id = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;
$exam = $this->exams->where('id', $exam_id)->first();


if(!$exam)
return $this->failNotFound('Unable to find the exam. ');

if($exam->user_id != $user_id)
return $this->fail(['message' => 'You are not allowed to access this resource. ']);


$db = db_connect();
$subquery = $db->table('question_sheet')->select('question_id')->where('entity_id', $exam->id);
//$questions = $db->table('questions')->join('exams', 'exams.id = questions.entity_id')->select('exams.title, questions.question, questions.type, questions.point, questions.id ')->whereNotIn('questions.id', $subquery)->orderBy('questions.created_at', 'desc')->get();
//$questions = $questions->getResult();
$questions = $this->model->join('exams', 'exams.id = questions.entity_id', 'left')->join('quizzes', 'quizzes.id = questions.entity_id', 'left')->select(' ifnull(exams.title, quizzes.title)as title, question, type, point, questions.id ')->whereNotIn('questions.id', $subquery)->where('questions.user_id', $user_id)->findAll();
return $this->respond($questions);

    }

    public function show($id = null)
    {
if(!$this->us->is_owner($id, 'questions'))
return $this->fail(['message' => 'You are not allowed to access this question. ']);

$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('questions', 'view'))
return $this->fail(['message' => $this->is->getError()]);
$question = $this->model->where('id', $id)->first();
if(!$question)
return $this->failNotFound('Unable to find the question');

return $this->respond($question);


    }


    public function create()
    {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('questions', 'create'))
return $this->fail(['message' => $this->is->getError()]);
$rules = [
'entity_id' => 'required|isExists[entity_id]',
'type' => 'required|in_list[single_choice,multi_choice,true_or_false,fill_the_blanks,match_it,descriptive,cloze,error_correction]|isAllowedType[type]',
'question' => 'required',
'options' => 'if_exist|checkType[options]',
'answer' => 'checkAnswer[answer]',
'point' => 'required|greater_than_equal_to[1]',
'mpoint' => 'required|less_than_equal_to[point]',
'explanation' => 'if_exist|string'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$request = (array) $this->request->getVar();
$request['user_id'] = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;

if($this->model->isQuiz($request['entity_id']) && $this->model->getQuestionCount($request['entity_id']) >= 30)
return $this->fail(['message' => 'Sorry, You can only add 30 questions to a quiz. Please try exams, If you need more questions.']);

$entity = new \App\Entities\Questions($request);
$id = $this->model->insert($entity);
$this->questionsheet->insert([
'question_id' => $id,
'entity_id' => $request['entity_id']]);

$count = $this->questionsheet->where('entity_id', $request['entity_id'])->countAllResults();

return $this->respond([
'message' => 'Successfully saved',
'count' => $count,
'id' => $id]);

    }
    public function update($id = null)
    {
if(!$this->us->is_owner($id, 'questions'))
return $this->fail(['message' => 'You cannot update or access this question. ']);
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('questions', 'edit'))
return $this->fail(['message' => $this->is->getError()]);
$request = (array) $this->request->getVar();

if(isset($request['entity_id']))
unset($request['entity_id']);

if(isset($request['user_id']))
unset($request['user_id']);

$entity = new \App\Entities\Questions($request);
$this->model->update($id, $entity);

return $this->respond(['id' => $id]);


    }

    public function delete($id = null)
    {
if(!$this->us->is_owner($id, 'questions'))
return $this->fail(['message' => ' You cannot delete this question. ']);
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('questions', 'delete'))
return $this->fail(['message' => $this->is->getError()]);

$this->questionsheet->where('question_id', $id)->delete();
//$this->model->where('id', $id)->delete();
return $this->respondDeleted('successfully deleted');

    }


public function clear($id) {
if(!$this->us->is_owner($id, 'quizzes') && !$this->us->is_owner($id, 'exams'))
return $this->fail(['message' => ' You have not permission to clear all questions in this resource. ']);
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('questions', 'delete'))
return $this->fail(['message' => $this->is->getError()]);
return $this->respondDeleted($this->questionsheet->where('entity_id', $id)->delete());
}

public function upload() {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('questions', 'create'))
return $this->fail(['message' => $this->is->getError()]);
$rules = [
'entity_id' => 'required',
'questions' => 'uploaded[questions]|ext_in[questions,xlsx,json,txt,toml,docx]'];

$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$type = null;


$request = (array) $this->request->getVar();

if(!$this->us->is_owner($request['entity_id'], 'quizzes') && !$this->us->is_owner($request['entity_id'], 'exams'))
return $this->fail(['message' => ' You have not permission to upload questions in this resource. ']);

if($this->exams->where('id', $request['entity_id'])->first())
$type = 'exam';
if($this->quizzes->where('id', $request['entity_id'])->first())
$type = 'quiz';

if(!$type)
return $this->fail(['message' => 'Unable to find the entity']);

$file = $this->request->getFile('questions');
if($file->isValid()) {
$validator = new \App\Libraries\Uploads\Validator($file, $type);
if(!$validator->run()) 
return $this->fail([
'message' => $validator->getError()]);

if($type == 'quiz' && $validator->getCount() > 30)
return $this->fail(['message' => 'The quiz can only contain 30 questions. If you want more questions, Please try exams.']);


} else {
return $this->fail(['message' => 'The file is invalid']);
}


$questions = $validator->getQuestions();
foreach($questions as $question) {
$question['entity_id'] = $request['entity_id'];
$question['user_id'] = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;
$questionEntity = new \App\Entities\Questions($question);

$question_id = $this->model->insert($questionEntity);
$this->questionsheet->insert([
'question_id' => $question_id,
'entity_id' => $request['entity_id']]);

}


return $this->respond([
'message' => 'success']);


}

public function add() {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('exams', 'edit'))
return $this->fail(['message' => $this->is->getError()]);
$user_id = $isInstructor ? $this->request->user->admin_id: $this->request->user->uuid;
$rules = [
'questions' => 'required',
'entity_id' => 'required|is_owner[entity_id,exams]|is_not_unique[exams.id]'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$request = (array) $this->request->getVar();
$exam = $this->exams->where('id', $request['entity_id'])->first();
if($exam->user_id != $user_id)
return $this->fail(['message' => 'You are not allowed to access this resource']);

if(!is_array($request['questions']))
return $this->fail(['message' => ' Please provide a list of question id to add. ']);
foreach($request['questions'] as $question_id) {
if(!$this->model->where('id', $question_id)->where('user_id', $user_id)->first()) 
return $this->fail(['message' => 'You have no permission to access this question. ']);

if(!$this->questionsheet->where('entity_id', $exam->id)->where('question_id', $question_id)->first())
$this->questionsheet->insert([
'entity_id' => $exam->id,
'question_id' => $question_id
]);
}

return $this->respondCreated('success');

}

public function remove() {
$isInstructor = $this->is->is_instructor();
if($isInstructor && !$this->is->has_access('exams', 'edit'))
return $this->fail(['message' => $this->is->getError()]);

$rules = [
'question_id' => 'required|is_owner[question_id,questions]|is_not_unique[questions.id]',
'entity_id' => 'required'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$request = (array) $this->request->getVar();


if($this->questionsheet->where('entity_id', $request['entity_id'])->where('question_id', $request['question_id'])->first())
$this->questionsheet->where('entity_id', $request['entity_id'])->where('question_id', $request['question_id'])->delete();

return $this->respond([
'id' => $request['question_id']]);
}

public function getCount() {
$entity_id = $this->request->getVar('entity_id');
if(!$entity_id)
return $this->fail(['message' => 'The id is required.']);
$count = $this->questionsheet->where('entity_id', $entity_id)->countAllResults();
return $this->respond(['count' => $count]);
}


}
