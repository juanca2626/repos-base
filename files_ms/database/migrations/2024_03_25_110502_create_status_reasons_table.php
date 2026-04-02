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
        Schema::create('status_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('status_iso')->nullable();
            $table->unsignedBigInteger('user_id')->default(1);
            $table->text('name')->nullable();
            $table->smallInteger('visible')->nullable()->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_reasons');
    }
};
