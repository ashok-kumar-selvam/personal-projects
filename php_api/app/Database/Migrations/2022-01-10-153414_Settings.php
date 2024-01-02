<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Settings extends Migration
{
    public function up()
    {
$this->forge->addField([

'id' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true],

'exam_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'assign_to' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'assignee_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => true],

'allowed_access' => [
'type' => 'int',
'constraint' => '30',
'default' => '0',
'null' => false],

'resumable' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'no',
'null' => false],

'allowed_emails' => [
'type' => 'json',
'null' => true],


'allowed_attempts' => [
'type' => 'int',
'constraint' => '50',
'default' => '0'],

'question_random' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'yes'],

'option_random' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'yes'],

'pass_mark' => [
'type' => 'float',
'constraint' => '30',
'default' => '0'],

'mpoint' => [
'type' => 'float',
'constraint' => '30',
'default' => '0'],

'start_time' => [
'type' => 'varchar',
'constraint' => '250',
'default' => '0',
'null' => false],

'end_time' => [
'type' => 'varchar',
'constraint' => '250',
'default' => '0',
'null' => false],

'time_limit' => [
'type' => 'int',
'constraint' => '250',
'default' => '0'],

'time_decrement' => [
'type' => 'int',
'constraint' => '250',
'default' => '0'],

'question_time' => [
'type' => 'int',
'constraint' => '50',
'default' => '0'],

'result_type' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'complete_result'],

'result_method' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'imediate'],

'publish_on' => [
'type' => 'varchar',
'constraint' => '250',
'default' => '0'],

'show_explanation' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'yes'],

'message' => [
'type' => 'text',
'constraint' => '50000',
'null' => true],

'is_active' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'no'],

'published' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'no'],

]);
$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addField("updated timestamp default current_timestamp on update current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('settings', true);

    }

    public function down()
    {
$this->forge->dropTable('settings', true);
    }
}
