<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsPackageTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_translations', function (Blueprint $table) {
            $table->text('description_commercial')->after('description')->nullable();
            $table->string('itinerary_link_commercial')->after('itinerary_link')->nullable();
            $table->text('itinerary_commercial')->after('itinerary_description')->nullable();
            $table->text('restriction_commercial')->after('restriction')->nullable();
            $table->text('policies_commercial')->after('policies')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_translations', function (Blueprint $table) {
            $table->dropColumn('description_commercial');
            $table->dropColumn('itinerary_link_commercial');
            $table->dropColumn('itinerary_commercial');
            $table->dropColumn('restriction_commercial');
            $table->dropColumn('policies_commercial');
        });
    }
}
