<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->float('total_amount', 10, 2)->after('total_services_taxes')->default(0.00);
            $table->float('total_tax', 10, 2)->after('total_services_taxes')->default(0.00);
            $table->float('subtotal_amount', 10, 2)->after('total_services_taxes')->default(0.00);
            $table->float('total_discounts', 10, 2)->after('total_services_taxes')->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            //
        });
    }
}
