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
        Schema::create('file_hotel_room_unit_nights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_hotel_room_unit_id')->constrained('file_hotel_room_units');
            $table->date('date');
            $table->smallInteger('number')->nullable();
            $table->decimal('price_adult_sale',8,2)->default(0);
            $table->decimal('price_adult_cost',8,2)->default(0);
            $table->decimal('price_child_sale',8,2)->default(0);
            $table->decimal('price_child_cost',8,2)->default(0);
            $table->decimal('price_infant_sale',8,2)->default(0);
            $table->decimal('price_infant_cost',8,2)->default(0);
            $table->decimal('price_extra_sale',8,2)->default(0);
            $table->decimal('price_extra_cost',8,2)->default(0);
            $table->decimal('total_amount_sale',8,2)->default(0);
            $table->decimal('total_amount_cost',8,2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_hotel_room_unit_nights');
    }
};
