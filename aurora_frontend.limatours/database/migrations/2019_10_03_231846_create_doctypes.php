<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoctypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctypes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('iso');
            $table->timestamps();
        });

        DB::table('doctypes')->insert(
            ['iso' => 'CEX']
        );

        DB::table('doctypes')->insert(
            ['iso' => 'DNI']
        );

        DB::table('doctypes')->insert(
            ['iso' => 'PAS']
        );

        DB::table('doctypes')->insert(
            ['iso' => 'RUC']
        );

        DB::table('doctypes')->insert(
            ['iso' => 'OTR']
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctypes');
    }
}
