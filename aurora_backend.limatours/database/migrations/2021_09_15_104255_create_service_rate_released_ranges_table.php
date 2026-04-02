<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceRateReleasedRangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_rate_released_ranges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_rate_released_id');
            $table->foreign('service_rate_released_id','srv_r_r_id')->references('id')->on('service_rate_released');
            $table->date('date_from');
            $table->date('date_to');

            $table->smallInteger('every');
            $table->enum('type_discount', ['percentage', 'amount'])->default('percentage');
            $table->double('value')->default(0);

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
        Schema::dropIfExists('service_rate_released_ranges');
    }
}
