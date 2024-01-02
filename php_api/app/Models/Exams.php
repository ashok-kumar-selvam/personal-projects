<?php

namespace App\Models;

use CodeIgniter\Model;

class Exams extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'exams';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $insertID         = false;
    protected $returnType       = \App\Entities\Exams::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'user_id', 'title', 'subject', 'description', 'is_ready'];

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
