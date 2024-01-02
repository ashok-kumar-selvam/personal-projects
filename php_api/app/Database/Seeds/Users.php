<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Users extends Seeder
{
    public function run()
    {
$users = new \App\Models\Users;
$users->insert([
'first_name' => 'ashokkumar',
'last_name' => 'selvam',
'email' => 'eduforak@gmail.com',
'password' => 'Vinayaga@1',
'mobile' => '8220085801',
'status' => 'approved',
'type' => 'member']);

    }
}
