<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ClaimedReferrals extends Migration
{
    public function up()
    {
$this->forge->addField([
'id' => [
'type' => 'int',
'constraint' => 250,
'auto_increment' => true,
'unsigned' => true,
],

'referrer' => [
'type' => 'varchar',
'constraint' => '250',
],

'referee' => [
'type' => 'varchar',
'constraint' => '250'],

'offer' => [
'type' => 'varchar',
'constraint' => '250'],

'created_at' => [
'type' => 'text',
'constraint' => '5000'],

]);
$this->forge->addKey('id', true);
$this->forge->createTable('claimed_referrals', true);
    }

    public function down()
    {
$this->forge->dropTable('claimed_referrals', true);
    }
}
