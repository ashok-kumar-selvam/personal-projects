<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Instructors extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false,
'unique' => true],

'name' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'email' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true,
'null' => false],

'user_id' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true,
'null' => false],

'password' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'admin_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'permissions' => [
'type' => 'json',
'null' => false,
],

'status' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'created',
'null' => false],
]);
$this->forge->addField('created_at timestamp default current_timestamp');
$this->forge->addField('updated_at timestamp default current_timestamp on update current_timestamp');
$this->forge->addKey('id', true);
$this->forge->createTable('instructors', true);

    }

    public function down()
    {
$this->forge->dropTable('instructors', true);
    }
}
