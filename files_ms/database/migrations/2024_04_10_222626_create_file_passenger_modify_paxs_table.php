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
        Schema::create('file_passenger_modify_paxs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_passenger_id')->nullable(); 
            $table->string('name')->nullable();
            $table->string('surnames')->nullable();
            $table->date('date_birth')->nullable();
            $table->enum('type', ['ADL', 'CHD', 'INF', 'TC', 'GUI'])->default('ADL');
            $table->char('suggested_room_type', 1)->nullable();            
            $table->longText('accommodation')->nullable();
            $table->decimal('cost_by_passenger', 8, 2)->nullable()->default(0);        
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_passenger_modify_paxs');
    }
};
