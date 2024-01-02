<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Quizzes extends Migration
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

'category' => [
'type' => 'varchar',
'constraint' => '250',
'null' => true],

'notes' => [
'type' => 'text',
'constraint' => '50000',
'null' => true],

'publish' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'no'],

'expires_on' => [
'type' => 'varchar',
'constraint' => '250',
'null' => true],

'member_only' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'no'
],

]);
$this->forge->AddField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('quizzes', true);


    }

    public function down()
    {
$this->forge->dropTable('quizzes', true);
    }
}
