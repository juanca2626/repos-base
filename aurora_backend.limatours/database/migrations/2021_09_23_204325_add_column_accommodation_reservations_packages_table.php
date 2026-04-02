<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAccommodationReservationsPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_packages', function (Blueprint $table) {
            $table->smallInteger('quantity_child_dbl')->default(0)->after('quantity_dbl');
            $table->smallInteger('quantity_child_tpl')->default(0)->after('quantity_tpl');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_packages', function (Blueprint $table) {
            //
        });
    }
}
