<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsServiceTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_translations', function (Blueprint $table) {
            $table->string('name', 250)->after('language_id')->nullable();
            $table->text('description_commercial')->after('description')->nullable();
            $table->text('itinerary_commercial')->after('itinerary')->nullable();
            $table->text('summary_commercial')->after('summary')->nullable();
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
            $table->dropColumn('name');
            $table->dropColumn('description_commercial');
            $table->dropColumn('itinerary_commercial');
            $table->dropColumn('summary_commercial');
        });
    }
}
