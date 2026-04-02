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
        Schema::create('file_hotel_room_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_hotel_room_id')->constrained('file_hotel_rooms');
            $table->boolean('status')->default(1);
            $table->bigInteger('channel_id');
            $table->string('confirmation_code')->nullable();
            $table->decimal('amount_sale',8,2)->default(0)->nullable();
            $table->decimal('amount_cost',8,2)->default(0)->nullable();
            $table->decimal('taxed_unit_sale',8,2)->default(0);
            $table->decimal('taxed_unit_cost',8,2)->default(0);
            $table->smallInteger('adult_num')->default(0);
            $table->smallInteger('child_num')->default(0);
            $table->smallInteger('infant_num')->default(0);
            $table->smallInteger('extra_num')->default(0);
            $table->bigInteger('reservations_rates_plans_rooms_id');
            $table->bigInteger('rates_plans_rooms_id');            
            $table->string('channel_reservation_code')->nullable();            
            $table->boolean('confirmation_status')->default(1)->comment('Estado de la habitacion OK = 1, RQ = 0');          
            $table->longText('policies_cancellation')->nullable();
            $table->longText('taxes_and_services')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_hotel_room_units');
    }
};
