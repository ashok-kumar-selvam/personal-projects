<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class QuizResults extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true],

'quiz_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'member_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'member_type' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'attempt' => [
'type' => 'int',
'constraint' => '30',
'default' => 1],

'total_time' => [
'type' => 'int',
'constraint' => '30',
'default' => 0],

'total_questions' => [
'type' => 'int',
'constraint' => '30',
'default' => 0],

'attempted_questions' => [
'type' => 'int',
'constraint' => '30',
'null' => 0],

'answered_questions' => [
'type' => 'int',
'constraint' => '30',
'default' => 0],

'correct_answers' => [
'type' => 'int',
'constraint' => '30',
'default' => 0],

'result' => [
'type' => 'json'],

]);

$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");

$this->forge->addKey('id', true);
$this->forge->createTable('quiz_results', true);

    }

    public function down()
    {
$this->forge->dropTable('quiz_results', true);

    }
}
