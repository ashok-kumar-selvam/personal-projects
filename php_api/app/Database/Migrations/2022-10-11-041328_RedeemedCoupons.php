<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RedeemedCoupons extends Migration
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

'user_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false,

],

'transaction_id' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false,
],

'code' => [
'type' => 'varchar',
'constraint' => '250',
'null' => false,
],

'created_at' => [
'type' => 'text',
'constraint' => '2000',
],

]);

$this->forge->addKey('id', true);
$this->forge->createTable('redeemed_coupons', true);
    }

    public function down()
    {
$this->forge->dropTable('redeemed_coupons', true);
    }
}
