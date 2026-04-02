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
        Schema::table('file_notes_external_housing', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('city')->comment('ID del usuario creador');
            $table->string('created_by_code')->nullable()->after('created_by')->comment('Código del usuario creador');
            $table->string('created_by_name')->nullable()->after('created_by_code')->comment('Nombre del usuario creador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_notes_external_housing', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'created_by_code', 'created_by_name']);
        });
    }
};
