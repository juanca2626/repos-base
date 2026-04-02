<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataTrainRailRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Artisan::call('db:seed', [
//            '--class' => TrainRailRoutesTableSeeder::class,
//        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::disableForeignKeyConstraints();
//        App\TrainRailRoute::truncate();
//        Schema::enableForeignKeyConstraints();
    }
}
