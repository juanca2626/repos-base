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
        Schema::table('file_hotel_rooms', function (Blueprint $table) {      
            $table->foreignId('file_amount_type_flag_id')->nullable()->constrained('file_amount_type_flags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_hotel_rooms', function (Blueprint $table) {
            $table->dropForeign(['file_amount_type_flag_id']);
            $table->dropColumn('file_amount_type_flag_id');
        });
    }
};
