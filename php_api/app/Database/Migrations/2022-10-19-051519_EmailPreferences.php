<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EmailPreferences extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'varchar',
'constraint' => '250',
'unique' => true,
'null' => false],

'user_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'members' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'yes',
'null' => false],


'offers' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'yes',
'null' => false],

'credits' => [
'type' => 'varchar',
'constraint' => '250',
'default' => 'yes',
'null' => false],

'created_at' => [
'type' => 'text',
'constraint' => '5000',
'null' => false],


'updated_at' => [
'type' => 'text',
'constraint' => '5000',
'null' => false],

]);
$this->forge->addKey('id', true);
$this->forge->createTable('email_preferences', true);
    }

    public function down()
    {
$this->forge->dropTable('email_preferences', true);
    }
}
