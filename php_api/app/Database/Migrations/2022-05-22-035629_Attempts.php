<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Attempts extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true],

'assign_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'member_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'status' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'started'],

'attempt' => [
'type' => 'int',
'constraint' => '30',
'null' => false],

'total_points' => [
'type' => 'float',
'constraint' => '30',
'null' => false],

]);
$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('attempts', true);
    }

    public function down()
    {
$this->forge->dropTable('attempts', true);
    }
}
