<?php

namespace App\Models;

use CodeIgniter\Model;

class Results extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'results';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $insertID         = false;
    protected $returnType       = \App\Entities\Results::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    =['id', 'name', 'member_type', 'member_id', 'started_on', 'completed_on', 'status', 'has_passed', 'attempt', 'total_questions', 'attempted_questions', 'answered_questions', 'correct_answers', 'total_points', 'taken_points', 'total_time', 'taken_time', 'review', 'deleted', 'answers', 'setting', 'exam'];

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
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
