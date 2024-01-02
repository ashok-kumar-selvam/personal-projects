<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreditsEarned extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '250',
'auto_increment' => true,
'unsigned' => true],

'user_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'plan_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'credit' => [
'type' => 'int',
'constraint' => '250',
'default' => 1,
'null' => false],

'priority' => [
'type' => 'int',
'constraint' => '10',
'default' => 1,
'null' => false],

'from' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'to' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'category' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'monthly_credits'],

]);

$this->forge->addField('created_at timestamp default current_timestamp');
$this->forge->addField('updated_at timestamp default current_timestamp on update current_timestamp');
$this->forge->addKey('id', true);
$this->forge->createTable('credits_earned', true);
    }

    public function down()
    {
$this->forge->dropTable('credits_earned', true);
    }
}
