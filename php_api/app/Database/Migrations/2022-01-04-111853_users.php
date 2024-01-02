<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Users extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true],
'first_name' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'last_name' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'email' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true,
'null' => false],

'mobile' => [
'type' => 'varchar',
'constraint' => '250'],

'password' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'type' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false,
'default' => 'member'],

'status' => [
'type' => 'varchar',
'constraint' => '128',
'default' => 'approved'],

'referral_code' => [
'type' => 'varchar',
'constraint' => '250',
'null' => true,
],


]);
$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");


$this->forge->createTable('users', true);
    }

    public function down()
    {
$this->forge->dropTable('users', true);
    }
}
