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
        Schema::table('file_statement_details', function (Blueprint $table) {
            $table->string('type_room',25)->nullable();
            $table->string('type_pax',25)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_statement_details', function (Blueprint $table) {
            $table->dropColumn('type_rooms');
            $table->dropColumn('type_pax');
        });
    }
};
