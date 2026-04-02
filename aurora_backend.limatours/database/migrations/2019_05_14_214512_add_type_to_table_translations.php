<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTypeToTableTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE translations CHANGE COLUMN type type ENUM(
                    'account',
                    'amenity',
                    'channel',
                    'chain',
                    'city',
                    'client',
                    'country',
                    'currency',
                    'district',
                    'facility',
                    'hotel',
                    'hotelcategory',
                    'hoteltype',
                    'meal',
                    'room',
                    'roomtype',
                    'state',
                    'suplement',
                    'typeclass',
                    'zone',
                    'tag',
                    'physicalintensity'

        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE translations CHANGE COLUMN type type ENUM(
                    'account',
                    'amenity',
                    'channel',
                    'chain',
                    'city',
                    'client',
                    'country',
                    'currency',
                    'district',
                    'facility',
                    'hotel',
                    'hotelcategory',
                    'hoteltype',
                    'meal',
                    'room',
                    'roomtype',
                    'state',
                    'suplement',
                    'typeclass',
                    'zone'
        ) NOT NULL");
    }
}
