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
        Schema::create('file_status_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files');
            $table->enum('status', ['OK', 'XL', 'BL', 'CE', 'PF'])->default('OK')->comment('status->OK (Vigente) / XL (Anulado) / BL (Bloqueado) / CE (Cerrado)  / PF (Por Facturar)');
            $table->foreignId('status_reason_id')->nullable()->constrained('status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_status_reasons');
    }
};
