<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTypeEcommercePrivacyTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE translations CHANGE COLUMN type type ENUM(
                    'account', 'amenity', 'channel', 'chain', 'city', 'client', 'country', 'currency', 'district', 'facility', 'hotel', 'hotelcategory', 'hoteltype', 'meal', 'room', 'roomtype', 'state', 'suplement', 'typeclass', 'zone', 'tag', 'physicalintensity', 'requirement', 'experience', 'classification', 'servicetype', 'servicealert', 'servicecategory', 'servicesubcategory', 'unitduration', 'servicetypeactivity', 'inclusion', 'unit', 'restriction', 'rates_plan', 'servicerate', 'label', 'taggroup', 'rate_policies', 'rate_plan_room', 'docs', 'service_cancellation_policies', 'traintemplate', 'trainratepolicy', 'trainrate', 'multimedia', 'tagservices', 'term', 'information_important_service', 'instruction', 'paymode', 'question_category', 'frequent_question','ecommerce_privacy_policy'
        ) NOT NULL");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE translations CHANGE COLUMN type type ENUM(
                    'account', 'amenity', 'channel', 'chain', 'city', 'client', 'country', 'currency', 'district', 'facility', 'hotel', 'hotelcategory', 'hoteltype', 'meal', 'room', 'roomtype', 'state', 'suplement', 'typeclass', 'zone', 'tag', 'physicalintensity', 'requirement', 'experience', 'classification', 'servicetype', 'servicealert', 'servicecategory', 'servicesubcategory', 'unitduration', 'servicetypeactivity', 'inclusion', 'unit', 'restriction', 'rates_plan', 'servicerate', 'label', 'taggroup', 'rate_policies', 'rate_plan_room', 'docs', 'service_cancellation_policies', 'traintemplate', 'trainratepolicy', 'trainrate', 'multimedia', 'tagservices', 'term', 'information_important_service', 'instruction', 'paymode', 'question_category', 'frequent_question'
        ) NOT NULL");

    }
}
