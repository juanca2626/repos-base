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
            $table->string('service_category_id')->nullable();
            $table->string('service_sub_category_id')->nullable();
            $table->string('service_type_id')->nullable();
            $table->longText('service_summary')->nullable();
            $table->longText('service_itinerary')->nullable();
        });

    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_itineraries', function (Blueprint $table) {
            $table->dropColumn('service_category_id'); 
            $table->dropColumn('service_sub_category_id'); 
            $table->dropColumn('service_type_id'); 
            $table->dropColumn('service_summary'); 
            $table->dropColumn('service_itinerary'); 
        });
    }
};
