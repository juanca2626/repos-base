<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateColumnTypeTableTranslations extends Migration
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
                    'unit'
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
                    'unit'
        ) NOT NULL");
    }
}
