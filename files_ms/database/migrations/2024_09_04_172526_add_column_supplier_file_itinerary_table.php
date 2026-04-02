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
            $table->char('service_supplier_code', 20)->nullable();
            $table->string('service_supplier_name')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_itineraries', function (Blueprint $table) {
            $table->dropColumn('supplier_code');
            $table->dropColumn('supplier_name'); 
        });
    }
};
