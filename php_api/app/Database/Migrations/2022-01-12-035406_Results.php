<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Results extends Migration
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

'name' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'member_type' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'started_on' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'completed_on' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'status' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'has_passed' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'no'],

'attempt' => [
'type' => 'int',
'constraint' => '30',
'null' => false],

'total_questions' => [
'type' => 'int',
'constraint' => '250',
'null' => false],

'attempted_questions' => [
'type' => 'int',
'constraint' => '250',
'null' => false],

'answered_questions' => [
'type' => 'int',
'constraint' => '250',
'null' => false],

'correct_answers' => [
'type' => 'int',
'constraint' => '250',
'null' => false],

'total_points' => [
'type' => 'float',
'constraint' => '30',
'null' => false],

'taken_points' => [
'type' => 'float',
'constraint' => '30',
'null' => false],

'total_time' => [
'type' => 'int',
'constraint' => '250',
'null' => false],

'taken_time' => [
'type' => 'int',
'constraint' => '250',
'null' => false],

'review' => [
'type' => 'text',
'constraint' => '50000',
'null' => true
],

'answers' => [
'type' => 'json',
'null' => true],

'setting' => [
'type' => 'json',
'null' => true],

'exam' => [
'type' => 'json',
'null' => true],
]);

$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('results', true);
    }

    public function down()
    {
$this->forge->dropTable('results', true);

    }
}
