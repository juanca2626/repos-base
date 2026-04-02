<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTypeClassificationToGaleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE galeries CHANGE COLUMN type type ENUM(
                    'hotel',
                    'room',
                    'client', 
                    'amenity', 
                    'facility',
                    'package',
                    'service',
                    'classification'              
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
                    'hotel',
                    'room',
                    'client', 
                    'amenity', 
                    'facility',
                    'package',
                    'service'     
        ) NOT NULL");
    }
}
