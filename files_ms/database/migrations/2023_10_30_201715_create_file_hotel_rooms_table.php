<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_hotel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_itinerary_id')->constrained('file_itineraries');
            $table->smallInteger('item_number');
            $table->smallInteger('total_rooms');
            $table->boolean('status')->default(1)->comment('"estado":status del registro OK=>Vigente, XL=Eliminado');
            $table->boolean('confirmation_status')->default(1)->comment("Estado de la habitacion OK = 1, RQ = 0");
            $table->bigInteger('rate_plan_id');
            $table->string('rate_plan_name');
            $table->string('rate_plan_code');
            $table->string('room_name');
            $table->string('room_type'); 
            $table->smallInteger('occupation');            
            $table->bigInteger('channel_id');
            $table->string('additional_information')->nullable();
            $table->smallInteger('total_adults')->default(0);
            $table->smallInteger('total_children')->default(0);
            $table->smallInteger('total_infants')->default(0);
            $table->smallInteger('total_extra')->default(0);
            $table->char('currency', 4)->default('USD');
            $table->decimal('amount_sale', 8, 2)->default(0);
            $table->decimal('amount_cost', 8, 2)->default(0);
            $table->decimal('taxed_sale', 8, 2)->default(0);
            $table->decimal('taxed_cost', 8, 2)->default(0);
            $table->decimal('total_amount', 8, 2)->default(0);
            $table->decimal('markup_created', 8, 2)->default(0);
            $table->decimal('total_amount_created', 8, 2)->default(0);
            $table->decimal('total_amount_provider', 8, 2)->default(0);
            $table->decimal('total_amount_invoice', 8, 2)->default(0);
            $table->decimal('total_amount_taxed', 8, 2)->default(0);
            $table->decimal('total_amount_exempt', 8, 2)->default(0);
            $table->decimal('taxes', 8, 2)->default(0);
            $table->boolean('use_voucher')->default(0);
            $table->boolean('use_itinerary')->default(1);
            $table->boolean('voucher_sent')->default(0);
            $table->smallInteger('voucher_number')->nullable();
            $table->boolean('use_accounting_document')->nullable();
            $table->smallInteger('accounting_document_sent')->nullable()
                ->comment('docemi->1 = ya esta facturado - 2= no esta facturado - 3 = No se factura');
            $table->smallInteger('branch_number')->nullable();
            $table->boolean('document_skeleton')->default(0);
            $table->boolean('document_purchase_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_hotel_rooms');
    }
};
