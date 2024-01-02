<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Plans extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '100',
'auto_increment' => true,
'unique' => true,
'unsigned' => true],

'user_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'plan' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'basic',
'null' => false],

'started_on' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'expires_on' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'status' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'active',
'null' => false],
]);

$this->forge->addField('created_at timestamp default current_timestamp');
$this->forge->addField('updated_at timestamp default current_timestamp on update current_timestamp');
$this->forge->addKey('id', true);
$this->forge->createTable('plans', true);
    }

    public function down()
    {
$this->forge->dropTable('plans', true);
    }
}
