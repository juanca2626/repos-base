<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCascadeDeleteToQuoteServiceRoomsHyperguestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_service_rooms_hyperguest', function (Blueprint $table) {
            // Eliminar la clave foránea actual
            $table->dropForeign(['quote_service_id']);

            // Volver a crearla con ON DELETE CASCADE
            $table->foreign('quote_service_id')
                  ->references('id')->on('quote_services')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
  
    }
}
