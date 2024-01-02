<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreditsSpent extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '250',
'auto_increment' => true,
'unsigned' => true],

'credit_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'user_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'member_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'exam_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'assign_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'date' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false,
],

]);

$this->forge->addField('created_at timestamp default current_timestamp');
$this->forge->addField('updated_at timestamp default current_timestamp on update current_timestamp');
$this->forge->addKey('id', true);
$this->forge->createTable('credits_spent', true);
    }

    public function down()
    {
$this->forge->dropTable('credits_spent', true);
    }
}
