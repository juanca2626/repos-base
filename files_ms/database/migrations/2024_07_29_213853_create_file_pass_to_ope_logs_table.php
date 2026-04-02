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
        Schema::create('file_pass_to_ope_logs', function (Blueprint $table) {
            $table->id();            
            $table->foreignId('file_id')->constrained('files');
            $table->text('type')->comment('send/is_in_ope');            
            $table->string('sede');            
            $table->longText('processed_services')->nullable(); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_pass_to_ope_logs');
    }
};
