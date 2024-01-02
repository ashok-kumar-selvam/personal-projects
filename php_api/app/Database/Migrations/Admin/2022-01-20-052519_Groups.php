<?php

namespace App\Database\Migrations\Admin;

use CodeIgniter\Database\Migration;

class Groups extends Migration
{
    public function up()
    {
$this->forge->addField([

'id' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true],


'user_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'name' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'description' => [
'type' => 'text',
'constraint' => '50000',
'null' => false],

]);
$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('groups', true);

    }

    public function down()
    {
$this->forge->dropTable('groups', true);
    }
}
