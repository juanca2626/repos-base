<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('file_itineraries', function (Blueprint $table) { 
            $table->integer('aurora_reservation_id')->nullable()->comment('este campo sirve para validar si ya se registro una reserva o no al momento de hacer un update, no no tiene valor usara otros campos para validar si es la misma y si sigue sin conseguir recien registra la reserva en la tabla itinerarios');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_itineraries', function (Blueprint $table) {
            $table->dropColumn('aurora_reservation_id'); 
        });
    }
};
