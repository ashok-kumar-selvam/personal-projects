<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Contacts extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '128',
'unsigned' => true,
'auto_increment' => true],

'name' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'email' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'message' => [
'type' => 'text',
'constraint' => '550000',
'null' => false],

'type' => [
'type' => 'varchar',
'constraint' => '128',
'default' => 'command'],

'status' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'pending'],

]);
$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('contacts', true);

    }

    public function down()
    {
$this->forge->dropTable('contacts', true);

    }
}
