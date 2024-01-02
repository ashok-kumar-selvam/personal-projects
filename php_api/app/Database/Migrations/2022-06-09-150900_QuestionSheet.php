<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class QuestionSheet extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '250',
'auto_increment' => true,
'unsigned' => true],

'question_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'entity_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'parent' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'yes'],


]);
$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('question_sheet', true);

    }

    public function down()
    {
$this->forge->dropTable('question_sheet', true);
    }
}
