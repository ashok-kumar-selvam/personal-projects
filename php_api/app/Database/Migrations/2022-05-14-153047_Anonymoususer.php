<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Anonymoususer extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true],

'name' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'email' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'entity' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'entity_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],
]);

$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addField("updated_at timestamp default current_timestamp on update current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('anonymous_users', true);
    }

    public function down()
    {
$this->forge->dropTable('anonymous_users', true);
    }
}
