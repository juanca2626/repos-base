<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnTrainTemplateIdTrainCancellationPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('train_cancellation_policies', function (Blueprint $table) {
            $table->dropColumn(['train_template_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('train_cancellation_policies', function (Blueprint $table) {
            $table->bigInteger('train_template_id')->nullable();
        });
    }
}
