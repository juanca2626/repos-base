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
        if (!Schema::hasColumn('file_status_reasons', 'status')) {
            Schema::table('file_status_reasons', function (Blueprint $table) {                          
                $table->enum('status', ['OK', 'XL', 'BL', 'CE', 'PF'])->default('OK')->comment('status->OK (Vigente) / XL (Anulado) / BL (Bloqueado) / CE (Cerrado)  / PF (Por Facturar)')->after('file_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('file_status_reasons', 'status')) {
            Schema::table('file_status_reasons', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
