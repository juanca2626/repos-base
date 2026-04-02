<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class UpdateColumnServiceTranslationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_translations', function (Blueprint $table) {
            $table->string('name_commercial', 250)->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->text('itinerary')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_translations', function (Blueprint $table) {
            $table->string('name_commercial', 250)->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->text('itinerary')->nullable(false)->change();
        });
    }
}
