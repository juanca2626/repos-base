<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBookingStateToExtensionDespegarServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('extension_despegar_services', function (Blueprint $table) {
            $table->string('booking_state')->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('extension_despegar_services', function (Blueprint $table) {
            $table->dropColumn('booking_state');
        });
    }
}
