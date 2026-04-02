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
        Schema::create('file_service_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_service_composition_id')
                ->constrained('file_service_compositions');
            $table->boolean('status')->default(0);
            $table->decimal('amount_sale',8,2)->default(0);
            $table->decimal('amount_cost',8,2)->default(0);
            $table->decimal('amount_sale_origin',8,2)->default(0);
            $table->decimal('amount_cost_origin',8,2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_service_units');
    }
};
