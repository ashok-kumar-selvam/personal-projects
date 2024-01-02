<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Additional extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '100',
'auto_increment' => true,
'unsigned' => true],

'assign_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'member_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'time' => [
'type' => 'int',
'constraint' => '250',
'default' => 0],

'attempt' => [
'type' => 'int',
'constraint' => '100',
'default' => 0],

'is_resumable' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'no'
],
]);
$this->forge->addKey('id', true);
$this->forge->createTable('additional', true);

    }

    public function down()
    {
$this->forge->dropTable('additional', true);
    }
}
