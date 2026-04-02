<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddTypeServiceToGaleriesTable extends Migration
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
                    'service'                
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
                    'package'                 
        ) NOT NULL");
    }
}
