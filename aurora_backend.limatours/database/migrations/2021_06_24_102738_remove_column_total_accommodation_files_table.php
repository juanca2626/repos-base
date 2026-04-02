<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveColumnTotalAccommodationFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn(['total_accommodation']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->smallInteger('total_accommodation')->nullable()->comment('(select count(*) from t41 where nroemp = 5 and nroref = t11.nroref and tipsvs = "H") as flag_hotel
  Es para saber si existe el acomodo del hotel');
        });
    }
}
