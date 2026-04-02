<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateColumnTypeTranslationTable extends Migration
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
                    'physicalintensity',
                    'requirement',
                    'experience',
                    'classification',
                    'servicetype',
                    'servicealert',
                    'servicecategory',
                    'servicesubcategory',
                    'unitduration',
                    'servicetypeactivity',
                    'inclusion',
                    'unit',
                    'restriction'
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
                    'typeclass',
                    'zone',
                    'tag',
                    'physicalintensity',
                    'requirement',
                    'experience',
                    'classification',
                    'servicetype',
                    'servicealert',
                    'servicecategory',
                    'servicesubcategory',
                    'unitduration',
                    'servicetypeactivity',
                    'inclusion',
                    'unit',
                    'restriction'
        ) NOT NULL");
    }
}
