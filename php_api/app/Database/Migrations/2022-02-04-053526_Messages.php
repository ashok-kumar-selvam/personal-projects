<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Messages extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '30',
'unsigned' => true,
'auto_increment' => true],

'from' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'to' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'subject' => [
'type' => 'text',
'constraint' => '5000'],

'message' => [
'type' => 'text',
'constraint' => '50000']]);

$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('messages', true);


    }

    public function down()
    {
$this->forge->dropTable('messages', true);

    }
}
