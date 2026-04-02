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
        Schema::create('file_temporary_service_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_temporary_service_composition_id');  
            $table->boolean('status')->default(0);
            $table->decimal('amount_sale',8,2)->default(0);
            $table->decimal('amount_cost',8,2)->default(0);
            $table->decimal('amount_sale_origin',8,2)->default(0);
            $table->decimal('amount_cost_origin',8,2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('file_temporary_service_composition_id', 'filetemporaryservicecomposition_id_foreign')->references('id')->on('file_temporary_service_compositions'); 
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_service_units');
    }
};
