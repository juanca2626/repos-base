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
            $table->boolean('waiting_reason_id')->nullable()->after('waiting_list');
            $table->string('waiting_reason_other_message')->nullable()->after('waiting_reason_id');     
            $table->string('waiting_confirmation_code')->nullable()->after('waiting_reason_other_message');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
