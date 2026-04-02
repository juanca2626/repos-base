<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['file', 'service', 'hotel']);
            $table->bigInteger('object_id')->nullable(true);
            $table->string('file_code', 25);
            $table->string('origin', 25)->default('API');
            $table->enum('action', ['cancellation'])->default('cancellation');
            $table->tinyInteger('status')->default(0)->comment('0 = pendiente ; 1 = procesado');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
