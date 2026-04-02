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
        Schema::table('file_categories', function (Blueprint $table) {        
            $table->dropColumn('name');
            $table->foreignId('file_id')->nullable()->after('id')->constrained('files');
            $table->foreignId('category_id')->nullable()->after('file_id')->constrained('categories');             
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
