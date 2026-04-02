<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValueColumsRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `rates`
                            CHANGE `num_adult` `num_adult` TINYINT(4) NULL DEFAULT NULL,
                            CHANGE `num_child` `num_child` TINYINT(4) NULL DEFAULT NULL,
                            CHANGE `num_infant` `num_infant` TINYINT(4) NULL DEFAULT NULL,
                            CHANGE `price_adult` `price_adult` DECIMAL(10, 4) NULL DEFAULT NULL,
                            CHANGE `price_child` `price_child` DECIMAL(10, 4) NULL DEFAULT NULL,
                            CHANGE `price_infant` `price_infant` DECIMAL(10, 4) NULL DEFAULT NULL,
                            CHANGE `price_extra` `price_extra` DECIMAL(10, 4) NULL DEFAULT NULL,
                            CHANGE `price_total` `price_total` DECIMAL(10, 4) NULL DEFAULT NULL");
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
