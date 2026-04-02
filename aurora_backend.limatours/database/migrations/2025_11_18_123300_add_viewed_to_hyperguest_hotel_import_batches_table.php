<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddViewedToHyperguestHotelImportBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hyperguest_hotel_import_batches', function (Blueprint $table) {
            $table->boolean('viewed')->default(false)->after('error_message')->comment('Indica si el usuario ha visto esta notificación');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hyperguest_hotel_import_batches', function (Blueprint $table) {
            $table->dropColumn('viewed');
        });
    }
}

