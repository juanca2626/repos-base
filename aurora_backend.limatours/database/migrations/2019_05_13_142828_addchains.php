<?php

use Illuminate\Database\Migrations\Migration;

class Addchains extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('chains')->insert(array(
            'name' => 'Cadena Casa Andina',
            'status' => 1,
            'created_at' => date('Y-m-d H:m:s')
        ));

        DB::table('chains')->insert(array(
            'name' => 'Cadena Libertador',
            'status' => 1,
            'created_at' => date('Y-m-d H:m:s')
        ));

        DB::table('chains')->insert(array(
            'name' => 'Cadena San Agustin',
            'status' => 1,
            'created_at' => date('Y-m-d H:m:s')
        ));

        DB::table('chains')->insert(array(
            'name' => 'Cadena Sonesta',
            'status' => 1,
            'created_at' => date('Y-m-d H:m:s')
        ));
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
