<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Attend extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => '100',
'auto_increment' => true,
'unsigned' => true],

'result_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false],

'time' =>[
'type' => 'int',
'constraint' => '100',
'default' => 0],
]);
$this->forge->addField("last_active timestamp default current_timestamp on update current_timestamp");
$this->forge->addKey('id', true);
$this->forge->createTable('attend', true);

    }

    public function down()
    {
$this->forge->dropTable('attend', true);
    }
}
