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
        Schema::create('file_hotel_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_itinerary_id')->constrained('file_itineraries');
            $table->boolean('reservation_for_send')->default(0);
            $table->boolean('for_assign')->default(0);
            $table->boolean('assigned')->default(0);
            $table->string('code_request_book')->nullable()->comment('"preped":"LIMHDZ", codigo del servicio/hotel a quien le pido la reserva');
            $table->string('code_request_invoice')->nullable()->comment('"prefac":"LIMHDZ", codigo del servicio/hotel quien me manda factura, a quien le pago');
            $table->string('code_request_voucher')->nullable()->comment('"prevou"=> codigo del servicio/hotel a quien va el voucher');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_hotel_suppliers');
    }
};
