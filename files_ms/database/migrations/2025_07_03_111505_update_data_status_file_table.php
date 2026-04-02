<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("update files set status_reason_id=1 where status='OK';");
        DB::statement("update files set status_reason_id=6 where status='XL';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
