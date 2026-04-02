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
        Schema::table('file_pass_to_ope_logs', function (Blueprint $table) {
            $table->string('sede');  
        });

         
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         
    }
};
