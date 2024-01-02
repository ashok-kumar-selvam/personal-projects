<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Keys extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '128',
'unsigned' => true,
'auto_increment' => true],
'from' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'system'],
'to' => [
'type' => 'varchar',
'constraint' => '250'],
'key' => [
'type' => 'varchar',
'constraint' => '250'],
'expire' => [
'type' => 'varchar',
'constraint' => '250'],


]);
$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");

$this->forge->addKey('id', true);
$this->forge->createTable('keys', true);
    }

    public function down()
    {
$this->forge->dropTable('keys', true);
    }
}
