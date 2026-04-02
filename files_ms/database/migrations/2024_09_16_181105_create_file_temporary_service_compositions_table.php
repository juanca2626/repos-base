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
        Schema::create('file_temporary_service_compositions', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('file_temporary_master_service_id');
            $table->unsignedBigInteger('file_classification_id'); 
            $table->unsignedBigInteger('type_composition_id'); 
            $table->unsignedBigInteger('type_component_service_id'); 
            $table->bigInteger('composition_id')->nullable();
            $table->char('code',6)->nullable();
            $table->string('name',250)->nullable();
            $table->smallInteger('item_number')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('rate_plan_code')->nullable()->comment('base de tarifas');
            $table->smallInteger('total_adults')->default(0);
            $table->smallInteger('total_children')->default(0);
            $table->smallInteger('total_infants')->default(0);
            $table->smallInteger('total_extra')->default(0);
            $table->boolean('is_programmable')->default(0);
            $table->boolean('is_in_ope')->default(0);
            $table->boolean('sent_to_ope')->default(0);            
            $table->char('country_in_iso', 4)->nullable();
            $table->string('country_in_name')->nullable();
            $table->char('city_in_iso', 4)->nullable();
            $table->string('city_in_name', 100)->nullable();
            $table->char('country_out_iso', 4)->nullable();
            $table->string('country_out_name')->nullable();
            $table->char('city_out_iso', 4)->nullable();
            $table->string('city_out_name', 100)->nullable();
            $table->time('start_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->date('date_in')->nullable();
            $table->date('date_out')->nullable();
            $table->char('currency', 4)->default('USD');
            $table->decimal('amount_sale', 8, 2)->default(0);
            $table->decimal('amount_cost', 8, 2)->default(0);
            $table->decimal('amount_sale_origin', 8, 2)->default(0);
            $table->decimal('amount_cost_origin', 8, 2)->default(0);
            $table->decimal('markup_created', 8, 2)->default(0);
            $table->decimal('taxes', 8, 2)->default(0);
            $table->smallInteger('total_services')->nullable();
            $table->boolean('use_voucher')->default(0);
            $table->boolean('use_itinerary')->default(0);
            $table->boolean('voucher_sent')->default(0);
            $table->smallInteger('voucher_number')->nullable();
            $table->boolean('use_ticket')->default(0);
            $table->boolean('use_accounting_document')->default(0);
            $table->boolean('ticket_sent')->default(0);
            $table->smallInteger('accounting_document_sent')->nullable()
                ->comment("docemi->SI = ya esta facturado - NO= no esta facturado - 00 = No se factura");
            $table->string('branch_number',45)->nullable();
            $table->boolean('document_skeleton')->default(0);
            $table->boolean('document_purchase_order')->nullable();
            $table->boolean('status')->default(1)
                ->comment('"estado":status del registro OK=> 1 Vigente, XL=0 Eliminado');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('file_temporary_master_service_id', 'filetemporarymasterserviceid_foreign')->references('id')->on('file_temporary_master_services'); 
            $table->foreign('file_classification_id', 'filefileclassificationid_foreign')->references('id')->on('file_classifications'); 
            $table->foreign('type_composition_id', 'filetypecompositionid_foreign')->references('id')->on('type_compositions'); 
            $table->foreign('type_component_service_id', 'filetypecomponentserviceid_foreign')->references('id')->on('type_component_services'); 
 

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_service_compositions');
    }
};
