<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Emails extends Migration
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
'constraint' => '250'],
'to' => [
'type' => 'varchar',
'constraint' => '250'],
'subject' => [
'type' => 'text',
'constraint' => '5000'],
'message' => [
'type' => 'text',
'constraint' => '50000'],


]);
$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");

$this->forge->addKey('id', true);
$this->forge->createTable('emails', true);
    }

    public function down()
    {
$this->forge->dropTable('emails', true);
    }
}
