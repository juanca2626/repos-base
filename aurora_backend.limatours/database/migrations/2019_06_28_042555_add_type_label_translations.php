<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTypeLabelTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `translations` CHANGE `type` `type` ENUM('account','amenity','channel','chain','city','client','country','currency','district','facility','hotel','hotelcategory','hoteltype','meal','room','roomtype','state','suplement','typeclass','zone','tag','physicalintensity','requirement','experience','classification','servicetype','servicealert','servicecategory','servicesubcategory','unitduration','servicetypeactivity','inclusion','unit','restriction','rates_plan','label')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `translations` CHANGE `type` `type` ENUM('account','amenity','channel','chain','city','client','country','currency','district','facility','hotel','hotelcategory','hoteltype','meal','room','roomtype','state','suplement','typeclass','zone','tag','physicalintensity','requirement','experience','classification','servicetype','servicealert','servicecategory','servicesubcategory','unitduration','servicetypeactivity','inclusion','unit','restriction','rates_plan')");
    }
}
