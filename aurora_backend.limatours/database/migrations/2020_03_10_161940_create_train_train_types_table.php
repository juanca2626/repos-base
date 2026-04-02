<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainTrainTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_train_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('train_id');
            $table->foreign('train_id')->references('id')->on('trains');
            $table->unsignedBigInteger('train_type_id');
            $table->foreign('train_type_id')->references('id')->on('train_types');
            $table->string('code');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('train_train_types')->insert([
            [
                "id" => 1,
                "train_id" => 1,
                "train_type_id" => 1,
                "code" => 1
            ],
            [
                "id" => 2,
                "train_id" => 1,
                "train_type_id" => 2,
                "code" => 2
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
        Schema::dropIfExists('train_train_types');
    }
}
