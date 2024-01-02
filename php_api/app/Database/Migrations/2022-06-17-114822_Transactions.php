<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Transactions extends Migration
{
    public function up()
    {
$this->forge->addField([

'id' => [
'type' => 'int',
'constraint' => '250',
'auto_increment' => true,
'unsigned' => true],


'amount' => [
'type' => 'float',
'constraint' => '30',
'null' => false],

'buyer' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'buyer_name' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'buyer_phone' => [
'type' => 'varchar',
'constraint' => '250',
'null' => true],

'payment_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'status' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'user_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'currency' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'fees' => [
'type' => 'varchar',
'constraint' => '250',
'null' => true],

'purpose' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],
]);
$this->forge->addField('created_at timestamp default current_timestamp');
$this->forge->addField('updated_at timestamp default current_timestamp on update current_timestamp');
$this->forge->addKey('id', true);
$this->forge->createTable('transactions', true);

    }

    public function down()
    {
$this->forge->dropTable('transactions', true);
    }
}
