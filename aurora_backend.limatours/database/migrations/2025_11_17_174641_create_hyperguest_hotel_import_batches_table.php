<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHyperguestHotelImportBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hyperguest_hotel_import_batches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('Usuario que inició la importación');
            $table->string('country')->nullable()->comment('País principal del lote de importación');
            $table->json('hotel_ids')->comment('IDs de los hoteles en Hyperguest (property_ids)');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->unsignedInteger('total_hotels')->default(0)->comment('Cantidad total de hoteles en el lote');
            $table->unsignedInteger('completed_hotels')->default(0)->comment('Cantidad de hoteles importados exitosamente');
            $table->unsignedInteger('failed_hotels')->default(0)->comment('Cantidad de hoteles que fallaron');
            $table->json('hotel_results')->nullable()->comment('Resultados de cada hotel: {property_id: hotel_id o null}');
            $table->text('error_message')->nullable()->comment('Mensaje de error general del lote');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('user_id');
            $table->index('country');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hyperguest_hotel_import_batches');
    }
}

