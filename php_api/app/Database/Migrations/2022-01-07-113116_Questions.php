<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Questions extends Migration
{
    public function up()
    {
$this->forge->addField([

'id' => [
'type' => 'int',
'constraint' => '128',
'auto_increment' => true,
'unsigned' => true],

'entity_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'user_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

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

'explanation' => [
'type' => 'text',
'constraint' => '50000',
'null' => true
],

'settings' => [
'type' => 'json',
'null' => true,
],

'point' => [
'type' => 'float',
'constraint' => '10',
'default' => 1],

'mpoint' => [
'type' => 'float',
'constraint' => '10',
'default' => 0],

]);
$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('questions', true);
    }

    public function down()
    {
$this->forge->dropTable('questions', true);
    }
}
