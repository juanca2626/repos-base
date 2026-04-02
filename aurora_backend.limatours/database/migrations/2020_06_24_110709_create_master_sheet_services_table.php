<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasterSheetServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_sheet_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('master_sheet_day_id');
            $table->foreign('master_sheet_day_id')->references('id')->on('master_sheet_days');
            $table->string('service_code_stela');
            $table->char('check_in',6)->nullable();
            $table->char('check_out',6)->nullable();
            $table->string('includes')->nullable();
            $table->string('description');
            $table->enum('type_service',['service','hotel','train']);
            $table->boolean('status')->default(1);
            $table->boolean('comment_status');
            $table->string('comment')->nullable();
            $table->string('attached')->nullable();
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
        Schema::dropIfExists('master_sheet_services');
    }
}
