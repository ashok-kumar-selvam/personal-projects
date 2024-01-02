<?php

namespace App\Models;

use CodeIgniter\Model;

class QuizResults extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'quiz_results';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $insertID         = 0;
    protected $returnType       = \App\Entities\QuizResults::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'quiz_id', 'member_id', 'member_type', 'attempt', 'total_time', 'total_questions', 'attempted_questions', 'answered_questions', 'correct_answers', 'result'];

    // Dates
    protected $useTimestamps = true;
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
    protected $beforeInsert   = ['PrimaryKey'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];


protected function PrimaryKey($data) {
helper('Model');
return primary_key($data, $this);
}
}
