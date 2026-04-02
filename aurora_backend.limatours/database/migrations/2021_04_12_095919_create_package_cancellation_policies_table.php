<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageCancellationPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_cancellation_policies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('pax_from');
            $table->integer('pax_to');
            $table->integer('day_from');
            $table->integer('day_to');
            $table->integer('cancellation_fees')->comment('Gastos de cancelacion en %');
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
        Schema::dropIfExists('package_cancellation_policies');
    }
}
