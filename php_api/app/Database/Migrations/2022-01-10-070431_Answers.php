<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Answers extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '128',
'auto_increment' => true,
'unsigned' => true],

'question_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'result_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'number' => [
'type' => 'int',
'constraint' => '30',
'null' => false],

'time' => [
'type' => 'int',
'constraint' => '100',
'default' => '0',
'null' => false],

'point' => [
'type' => 'float',
'constraint' => '30',
'default' => '0'],

'type' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'question' => [
'type' => 'json',
'null' => false],

'options' => [
'type' => 'json',
'null' => true],

'answer' => [
'type' => 'json',
'null' => true],

'has_attempted' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'no'],

'has_answered' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'no'],

'has_marked' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'no'],

'has_next' => [
'type' => 'varchar',
'constraint' => '250'],

'has_previous' => [
'type' => 'varchar',
'constraint' => '250'],

'is_correct' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'no'],
]);

$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('answers', true);

    }

    public function down()
    {
$this->forge->dropTable('answers', true);

    }
}
