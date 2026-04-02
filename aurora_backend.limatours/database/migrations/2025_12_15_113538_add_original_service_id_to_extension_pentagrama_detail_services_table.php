<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginalServiceIdToExtensionPentagramaDetailServicesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('extension_pentagrama_detail_services', function (Blueprint $table) {
            $table->string('original_service_id', 100)
                ->nullable()
                ->after('type_service')
                ->comment('Código original recibido del proveedor (ej: TRNE94+MPI515)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('extension_pentagrama_detail_services', function (Blueprint $table) {
            $table->dropColumn('original_service_id');
        });
    }
}
