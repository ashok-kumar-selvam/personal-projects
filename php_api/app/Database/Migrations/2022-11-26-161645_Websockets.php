<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Websockets extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '250',
'auto_increment' => true,
'unsigned' => true,
],

'connection_id' => [
'type' => 'varchar',
'constraint' => '250'],

'name' => [
'type' => 'varchar',
'constraint' => '250',
'null' => true],

'user_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => true],

'exam_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => true],

'type' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'member'],

]);
$this->forge->addKey('id', true);
$this->forge->createTable('websockets');
    }

    public function down()
    {
$this->forge->dropTable('websockets', true);
    }
}
