<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Payments extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '100',
'auto_increment' => true,
'unique' => true],

'transaction_id' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true,
'null' => false],

'user_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'amount' => [
'type' => 'float',
'constraint' => '30',
'null' => false],

'plan' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'status' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'initiated',
'null' => false],

'type' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'premium',
'null' => false],

'coupon' => [
'type' => 'varchar',
'constraint' => '250',
'null' => true],


]);

$this->forge->addField('created_at timestamp default current_timestamp');
$this->forge->addField('updated_at timestamp default current_timestamp on update current_timestamp');
$this->forge->addKey('id', true);
$this->forge->createTable('payments', true);


    }

    public function down()
    {
$this->forge->dropTable('payments', true);
    }
}
