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
        Schema::table('files', function (Blueprint $table) {
        
            $table->integer('suggested_accommodation_sgl')->nullable()->after('passenger_changes');
            $table->integer('suggested_accommodation_dbl')->nullable()->after('suggested_accommodation_sgl');
            $table->integer('suggested_accommodation_tpl')->nullable()->after('suggested_accommodation_dbl');
            $table->foreignId('file_category_id')->nullable()->after('suggested_accommodation_tpl')->constrained('file_categories');
            $table->integer('generate_statement')->nullable()->after('file_category_id'); 
            $table->foreignId('file_reason_statement_id')->nullable()->after('generate_statement')->constrained('file_reason_statement');
            $table->smallInteger('type_class_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('suggested_accommodation_sgl');
            $table->dropColumn('suggested_accommodation_dbl');
            $table->dropColumn('suggested_accommodation_tpl');
            $table->dropColumn('file_category_id');
            $table->dropColumn('generate_statement');
            $table->dropColumn('file_reason_statement_id');
        });
    }
};
