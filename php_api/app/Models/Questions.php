<?php

namespace App\Models;

use CodeIgniter\Model;

class Questions extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'questions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = \App\Entities\Questions::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['entity_id', 'user_id', 'type', 'question', 'options', 'answer', 'explanation', 'point', 'mpoint', 'settings'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

public function upload(array $array, string $entity_id, string $user_id) {
$questionsheet = new \App\Models\QuestionSheet;
foreach($array as $object) {
$object = (array) $object;
$object['user_id'] = $user_id;
$object['entity_id'] = $entity_id;
$entity = new \App\Entities\Questions($object);
$question_id = $this->insert($entity);
$questionsheet->insert([
'question_id' => $question_id,
'entity_id' => $entity_id]);

}
return true;
}

public function isQuiz($entity_id) {
$quizzes = new \App\Models\Quizzes;
$quiz = $quizzes->where('id', $entity_id)->first();
if(!$quiz)
return false;
return true;
}

public function getQuestionCount($entity_id) {
$QuestionSheets = new \App\Models\QuestionSheet;
return $QuestionSheets->where('entity_id', $entity_id)->countAllResults();
}



}
