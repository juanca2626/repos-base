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
        Schema::table('file_service_compositions', function (Blueprint $table) { 
            $table->string('type_service')->nullable()->after('name'); 
            $table->string('confirmation_status')->default(0)->after('status');
            $table->boolean('waiting_list')->default(0)->after('confirmation_status');
            $table->boolean('waiting_reason_id')->nullable()->after('waiting_list');
            $table->string('waiting_reason_other_message')->nullable()->after('waiting_reason_id');     
            $table->string('waiting_confirmation_code')->nullable()->after('waiting_reason_other_message'); 
            $table->string('confirmation_code')->nullable(); 
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
