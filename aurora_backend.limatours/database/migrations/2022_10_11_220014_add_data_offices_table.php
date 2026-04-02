<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $created_at = date("Y-m-d H:i:s");

        DB::table('offices')->insert([
            'name' => 'Lima',
            'state_id' => 1610,
            'phone' => null,
            'address' => null,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('offices')->insert([
            'name' => 'Cusco',
            'state_id' => 1603,
            'phone' => null,
            'address' => null,
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
