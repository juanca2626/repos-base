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
        Schema::create('file_room_amount_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_amount_type_flag_id')->constrained('file_amount_type_flags');
            $table->foreignId('file_amount_reason_id')->constrained('file_amount_reasons');
            $table->foreignId('file_hotel_room_id')->constrained('file_hotel_rooms');
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount_previous', 8, 2)->default(0);
            $table->decimal('amount', 8, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_room_amount_logs');
    }
};
