<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInstructionsField extends Migration
{
    public function up()
    {
$this->forge->addColumn('settings', [
'instructions' => [
'type' => 'json',
'null' => true,
],
]);

    }

    public function down()
    {
        //
    }
}
