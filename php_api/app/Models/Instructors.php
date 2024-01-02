<?php

namespace App\Models;

use CodeIgniter\Model;

class Instructors extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'instructors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $insertID         = 0;
    protected $returnType       = \App\Entities\Instructors::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'email', 'user_id', 'password', 'admin_id', 'permissions', 'status'];

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
$data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
return primary_key($data, $this);
}


}
