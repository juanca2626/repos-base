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
        Schema::table('file_note_status_history', function (Blueprint $table) {
            $table->string('user_by_code')->nullable()->after('user_id')->comment('Código del usuario que creó el registro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_note_status_history', function (Blueprint $table) {
           $table->dropColumn('user_by_code');
        });
    }
};
