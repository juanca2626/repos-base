<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $created_at = date("Y-m-d H:i:s");

        DB::table('positions')->insert([
            'name' => 'Asistente',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('positions')->insert([
            'name' => 'Director',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('positions')->insert([
            'name' => 'Director General',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('positions')->insert([
            'name' => 'Especialista',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('positions')->insert([
            'name' => 'Gerente',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('positions')->insert([
            'name' => 'Jefe',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('positions')->insert([
            'name' => 'Supervisor(a)',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);

        DB::table('positions')->insert([
            'name' => 'Practicante',
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
