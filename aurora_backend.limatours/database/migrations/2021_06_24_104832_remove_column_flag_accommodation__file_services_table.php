<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveColumnFlagAccommodationFileServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_services', function (Blueprint $table) {
            $table->dropColumn(['flag_accommodation']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_services', function (Blueprint $table) {
            $table->boolean('flag_accommodation')->nullable()->comment('"flag_acomodo"="1", Cuando esta asignado a un pax');
        });
    }
}
