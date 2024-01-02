<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Exams extends Migration
{
    public function up()
    {

$this->forge->addField([
'id' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true],

'user_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'title' => [
'type' => 'text',
'constraint' => '50000',
'null' => false],

'subject' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'description' => [
'type' => 'text',
'constraint' => '50000',
'null' => false],


]);
$this->forge->AddField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");
$this->forge->createTable('exams', true);

    }

    public function down()
    {
$this->forge->dropTable('exams', true);
    }
}
