<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->char('abbreviation', 2);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('train_types')->insert([
            [
                "id" => 1,
                "name" => "One Way",
                "abbreviation" => "OW"
            ],
            [
                "id" => 2,
                "name" => "Round Trip",
                "abbreviation" => "RT"
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('train_types');
    }
}
