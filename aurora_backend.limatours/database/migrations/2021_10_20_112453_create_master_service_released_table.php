<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasterServiceReleasedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_service_released', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('master_service_id');
            $table->foreign('master_service_id','ms_id')->references('id')->on('master_services');
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
        Schema::dropIfExists('master_service_released');
    }
}
