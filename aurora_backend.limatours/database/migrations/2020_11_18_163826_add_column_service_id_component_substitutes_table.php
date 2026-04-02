<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnServiceIdComponentSubstitutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('component_substitutes', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->after("component_id");
            $table->foreign("service_id")->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('component_substitutes', function (Blueprint $table) {
            $table->dropForeign('component_substitutes_service_id_foreign');
            $table->dropColumn('service_id');
        });
    }
}
