<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportHyperguestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_hyperguests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('month');
            $table->string('year');     
            $table->decimal('fee',10,2);
            $table->decimal('total_hyperguest',10,2);
            $table->decimal('fees_hyperguest',10,2);
            $table->decimal('total_aurora',10,2);
            $table->decimal('fees_aurora',10,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_hyperguests');
    }
}
