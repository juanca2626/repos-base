<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsReservationsPassengers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservation_passengers', function (Blueprint $table) {
            $table->enum('genre', ['M', 'F'])->default('M')->after('date_birth');
            $table->enum('type', ['ADL', 'CHD', 'INF'])->default('ADL')->after('date_birth');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservation_passengers', function (Blueprint $table) {
            $table->dropColumn('genre');
            $table->dropColumn('type');
        });
    }
}
