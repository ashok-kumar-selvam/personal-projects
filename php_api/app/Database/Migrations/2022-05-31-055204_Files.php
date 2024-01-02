<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Files extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true],

'member_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'result_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'question_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'name' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'path' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'type' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'size' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],
]);
$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('files', true);
    }

    public function down()
    {
$this->forge->dropTable('files', true);
    }
}
