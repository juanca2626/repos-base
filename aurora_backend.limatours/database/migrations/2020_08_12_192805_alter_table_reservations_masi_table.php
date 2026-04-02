<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableReservationsMasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('reservation_billing_id')->nullable();
            $table->foreign('reservation_billing_id')->references('id')->on('reservation_billings');
            $table->tinyInteger('status_cron_job_reservation_stella')->default(0)->comment("(0) crear cliente de reservation billing en stella (1) generar file en stella (2) generar asiento contable en stella");
            $table->string('payment_code')->nullable();
            $table->string('accounting_seat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
