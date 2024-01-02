<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Offers extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '250',
'auto_increment' => true,
'unsigned' => true],

'name' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false,
'default' => 'New offer'],

'description' => [
'type' => 'text',
'constraint' => '5000',

],

'code' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true,
'null' => false,
],

'discount' => [
'type' => 'int',
'constraint' => '250',
'default' => '10',
'null' => false,
],

'expiry' => [
'type' => 'text',
'constraint' => '250',
'null' => false,
],

'limit' => [
'type' => 'int',
'constraint' => '250',
'default' => '0',
'null' => false,
],

'product' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false,
'default' => 'all',
],

'target' => [
'type' => 'text',
'constraint' => '5000',
'null' => false,
],
]);

$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('offers', true);
    }

    public function down()
    {
$this->forge->dropTable('offers', true);
    }
}
