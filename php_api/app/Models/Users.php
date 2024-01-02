<?php

namespace App\Models;

use CodeIgniter\Model;

class Users extends Model
{
    protected $DBGroup              = 'default';
    protected $table                = 'users';
    protected $primaryKey           = 'id';
    protected $useAutoIncrement     = false;
protected $insertId = 0;
    protected $returnType           = 'App\Entities\Users';
    protected $useSoftDeletes       = false;
    protected $protectFields        = true;
    protected $allowedFields        = ['first_name', 'last_name', 'email', 'mobile', 'password', 'type', 'status', 'referral_code'];

    // Dates
    protected $useTimestamps        = false;
    protected $dateFormat           = 'datetime';
    protected $createdField         = 'created_at';
    protected $updatedField         = 'updated_at';
    protected $deletedField         = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks       = true;
    protected $beforeInsert         = ['PrimaryKey'];
    protected $afterInsert          = [];
    protected $beforeUpdate         = [];
    protected $afterUpdate          = [];
    protected $beforeFind           = [];
    protected $afterFind            = [];
    protected $beforeDelete         = [];
    protected $afterDelete          = [];
protected function PrimaryKey($data) {
helper('Model');

$data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
$data['data']['referral_code'] = $this->getReferralCode();
$au = new \App\Models\AnonymousUsers;
$user = $au->where('email', $data['data']['email'])->orderBy('created_at', 'desc')->first();
if(!$user) {
return primary_key($data, $this);
} else {
$data['data']['id'] = $user->id;
return $data;
}

}

public function getReferralCode() {
helper('text');
while(true) {

$code = random_string('alnum', 5);
if(!$this->where('referral_code', $code)->first())
return $code;

}

}

public function setReferralCode($user_id) {
$code = $this->getReferralCode();
$this->where('id', $user_id)->set('referral_code', $code)->update();
return $code;
}


}
