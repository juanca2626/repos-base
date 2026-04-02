<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeTrainToGaleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE galeries CHANGE COLUMN type type ENUM(
                    'hotel', 'room', 'client', 'amenity', 'facility', 'package', 'service', 'classification', 'train'             
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE galeries CHANGE COLUMN type type ENUM(
                    'hotel', 'room', 'client', 'amenity', 'facility', 'package', 'service', 'classification'     
        ) NOT NULL");
    }
}
