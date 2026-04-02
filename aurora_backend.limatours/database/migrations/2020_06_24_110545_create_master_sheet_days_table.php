<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasterSheetDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_sheet_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('master_sheet_id');
            $table->foreign('master_sheet_id')->references('id')->on('master_sheets');
            $table->tinyInteger('number');
            $table->date('date_in');
            $table->string('destinations')->nullable();
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
        Schema::dropIfExists('master_sheet_days');
    }
}
