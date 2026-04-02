<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnTagServiceIdTableServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('masi');

            $table->unsignedBigInteger('tag_service_id')->nullable();
            $table->foreign('tag_service_id')->references('id')->on('tag_services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('masi')->default(false);
            $table->dropForeign('tag_service_id');
            $table->dropColumn('tag_service_id');
        });
    }
}
