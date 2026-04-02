<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeErrorMessageToJsonInHyperguestHotelImportBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Usar SQL directo para evitar problemas con Doctrine DBAL en Laravel 5.8
        DB::statement('ALTER TABLE `hyperguest_hotel_import_batches` MODIFY `error_message` JSON NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir el cambio: de json a text
        DB::statement('ALTER TABLE `hyperguest_hotel_import_batches` MODIFY `error_message` TEXT NULL');
    }
}

