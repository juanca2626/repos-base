<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files');
            $table->smallInteger('sequence_number')->nullable();
            $table->smallInteger('order_number')->nullable();
            $table->char('frequent', 20)->nullable();
            $table->char('document_type_id', 20)->nullable();
            $table->char('doctype_iso', 5)->nullable();
            $table->char('document_number', 20)->nullable();
            $table->string('name')->nullable();
            $table->string('surnames')->nullable();
            $table->date('date_birth')->nullable();
            $table->enum('type', ['ADL', 'CHD', 'INF', 'TC', 'GUI'])->default('ADL');
            $table->char('suggested_room_type', 1)->nullable();
            $table->char('genre', 1)->default('M');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->char('country_iso', 5)->nullable();
            $table->char('city_iso', 4)->nullable();
            $table->text('dietary_restrictions')->nullable();
            $table->text('medical_restrictions')->nullable();
            $table->text('notes')->nullable();
            $table->longText('accommodation')->nullable();        
            $table->decimal('cost_by_passenger', 8, 2)->nullable()->default(0);        
            $table->longText('document_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_passengers');
    }
};
