<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionSheet extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'question_sheet';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['question_id', 'entity_id', 'parent'];

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
    protected $beforeInsert   = ['isParent'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

protected function isParent($data) {
$questions = new \App\Models\Questions;

$question = $questions->where('id', $data['data']['question_id'])->first();
if($data['data']['entity_id'] == $question->entity_id) {
$data['data']['parent'] = 'yes';
} else {
$data['data']['parent'] = 'no';
}

return $data;

}

public function getQuestionCount($entity_id) {
return $this->where('entity_id', $entity_id)->countAllResults();
}

public function stats($entity_id) {
$stats = $this->join('questions', 'questions.id = question_sheet.question_id')->select('count(question_sheet.entity_id) as total_questions, sum(questions.point) as total_points ')->where('question_sheet.entity_id', $entity_id)->first();
return (array) $stats;
}

public function getPoints($entity_id) {
$stats = (array) $this->join('questions', 'questions.id = question_sheet.question_id')->select('count(question_sheet.entity_id) as total_questions, sum(questions.point) as total_points ')->where('question_sheet.entity_id', $entity_id)->first();
return $stats['total_points'];
}

}
