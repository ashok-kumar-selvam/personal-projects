<?php

namespace App\Database\Migrations\Admin;

use CodeIgniter\Database\Migration;

class GroupMembers extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '128',
'unsigned' => true,
'auto_increment' => true],

'group_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'member_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],
]);
$this->forge->addField("created_at timestamp default current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('group_members', true);
    }

    public function down()
    {
$this->forge->dropTable('group_members', true);
    }
}
