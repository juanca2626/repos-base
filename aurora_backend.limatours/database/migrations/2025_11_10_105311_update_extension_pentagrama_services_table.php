<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateExtensionPentagramaServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('extension_pentagrama_services')) {
            Schema::create('extension_pentagrama_services', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('quote_number')->nullable();
                $table->string('passenger')->nullable();
                $table->string('file_number')->nullable();
                $table->enum('status', ['pending', 'processed', 'canceled'])->default('pending');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extension_pentagrama_services');
    }
}
