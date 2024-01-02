<?php

namespace App\Models;

use CodeIgniter\Model;

class Settings extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $insertID         = 0;
    protected $returnType       = '\App\Entities\Settings';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
'id',
'exam_id',
'assign_to',
'assignee_id',
'allowed_access',
'allowed_emails',
'resumable',
'allowed_attempts',
'question_random',
'option_random',
'mpoint',
'pass_mark',
'start_time',
'end_time',
'time_limit',
'time_decrement',
'question_time',
'result_type',
'result_method',
'publish_on',
'published',
'show_explanation',
'message',
'instructions',
'is_active'];

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
