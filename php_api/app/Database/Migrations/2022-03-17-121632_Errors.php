<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Errors extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '100',
'unique' => true,
'auto_increment' => true,
'unsigned' => true],

'page' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'error' => [
'type' => 'text',
'constraint' => '5000'],
]);
$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('errors', true);
    }

    public function down()
    {
$this->forge->dropTable('errors', true);

    }
}
