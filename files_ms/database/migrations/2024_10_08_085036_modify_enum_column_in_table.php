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
               DB::statement("ALTER TABLE file_itineraries MODIFY COLUMN entity ENUM('hotel', 'service' , 'service-mask', 'service-temporary', 'flight','service-zero')");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       
    }
};
