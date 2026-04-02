<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainCancellationPolicyTrainPolicyRateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_cancellation_policy_train_policy_rate', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('train_policy_rate_id');
            $table->unsignedBigInteger('train_cancellation_policy_id');

            $table->foreign('train_policy_rate_id', 'tr_ca_po_tr_po_ra_tr_po_ra_id_foreign')
                ->references('id')->on('train_policy_rates');
            $table->foreign('train_cancellation_policy_id','tr_ca_po_tr_po_ra_tr_ca_po_id_foreign')
                ->references('id')->on('train_cancellation_policies');
//            train_cancellation_policy_train_policy_rate_train_cancellation_policy_id_foreign
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
        Schema::dropIfExists('train_cancellation_policy_train_policy_rate');
    }
}
