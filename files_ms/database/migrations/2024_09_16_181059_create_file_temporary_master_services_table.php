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
 
        Schema::create('file_temporary_master_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_temporary_service_id')->constrained('file_temporary_services');
            $table->bigInteger('master_service_id')->unsigned();
            $table->string('name');
            $table->char('code', 6);
            $table->string('type_ifx')->nullable()->comment('package o direct", sería para identificar como vino de informix, si es el caso.');
            $table->boolean('status')->default(1);
            $table->boolean('confirmation_status')->default(1)->comment('Estado de la habitacion OK = 1, RQ = 0');
            $table->date('date_in');
            $table->date('date_out');
            $table->time('start_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->decimal('amount_cost', 8, 2)->default(0);  
            $table->string('rate_plan_code')->nullable()->comment('base de tarifas');          
            $table->boolean('is_in_ope')->default(0);
            $table->boolean('sent_to_ope')->nullable()->default(0); 
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_master_services');
    }
};
