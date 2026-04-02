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
        Schema::create('file_temporary_service_composition_suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_temporary_service_composition_id'); 
            $table->boolean('reservation_for_send')->default(0);
            $table->boolean('for_assign')->default(0);
            $table->boolean('assigned')->default(0);
            $table->string('code_request_book')->nullable()->comment('"preped":"LIMHDZ", codigo del servicio/hotel a quien le pido la reserva');
            $table->string('code_request_invoice')->nullable()->comment('"prefac":"LIMHDZ", codigo del servicio/hotel quien me manda factura, a quien le pago');
            $table->string('code_request_voucher')->nullable()->comment('"prevou"=> codigo del servicio/hotel a quien va el voucher');
            $table->longText('policies_cancellation_service')->nullable();
            $table->boolean('send_communication')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('file_temporary_service_composition_id', 'filetemporaryservicecompositionid_foreign')->references('id')->on('file_temporary_service_compositions'); 
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_service_composition_suppliers');
    }
};
