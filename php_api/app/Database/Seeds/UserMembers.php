<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserMembers extends Seeder
{
    public function run()
    {
$users = new \App\Models\Users;
$user_members = new \App\Models\Admin\UserMembers;

$user = $users->where('email', 'eduforak@gmail.com')->first();
for($i = 1; $i < 5; $i++) {

$newUser = ['first_name' => "first $i",  'last_name' => "last $i",  'email' => 'ashok'.$i.'@g.com', 'password' => mt_rand(10000, 100000000), 'mobile' => mt_rand(100000000, 1000000000), 'type' => 'member', 'status' => 'approved'];
$member_id = $users->insert($newUser);
$user_members->insert([
'user_id' => $user->id,
'member_id' => $member_id]);
}

    }
}
