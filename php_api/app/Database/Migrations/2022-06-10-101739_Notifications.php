<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Notifications extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '250',
'auto_increment' => true,
'unsigned' => true],

'to' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'everyone'],

'member_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'priority' => [
'type' => 'int',
'constraint' => '250',
'default' => 5],

'message' => [
'type' => 'text',
'constraint' => '50000',
'null' => false],

'action' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'created'],

]);

$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('notifications', true);
    }

    public function down()
    {
$this->forge->dropTable('notifications', true);
    }
}
