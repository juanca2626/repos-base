<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_rate_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('train_type_id');
            $table->foreign('train_type_id')->references('id')->on('train_types');
            $table->unsignedBigInteger('train_rate_id');
            $table->foreign('train_rate_id')->references('id')->on('train_rates');
            $table->unsignedBigInteger('train_cancellation_policy_id');
            $table->foreign('train_cancellation_policy_id','tr_ca_po_tr_ra_pl_tr_ca_po_id_foreign')
                ->references('id')->on('train_cancellation_policies');
            $table->date('date_from');
            $table->date('date_to');
            $table->boolean('monday')->nullable();
            $table->boolean('tuesday')->nullable();
            $table->boolean('wednesday')->nullable();
            $table->boolean('thursday')->nullable();
            $table->boolean('friday')->nullable();
            $table->boolean('saturday')->nullable();
            $table->boolean('sunday')->nullable();
            $table->integer('pax_from');
            $table->integer('pax_to');
            $table->float('price_adult', 8, 2);
            $table->float('price_child', 8, 2);
            $table->float('price_infant', 8, 2);
            $table->float('price_guide', 8, 2);
            $table->boolean('status')->default(1);
            $table->string('frequency_code');
            $table->string('equivalence_code');
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
        Schema::dropIfExists('train_rate_plans');
    }
}
