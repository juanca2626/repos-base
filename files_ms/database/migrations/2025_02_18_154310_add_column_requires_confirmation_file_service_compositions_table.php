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
        Schema::table('file_service_compositions', function (Blueprint $table) {
            $table->char('requires_confirmation',2)->nullable()->after('type_service'); ;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_service_compositions', function (Blueprint $table) {
            $table->dropColumn('requires_confirmation'); ;
        });
    }
};
