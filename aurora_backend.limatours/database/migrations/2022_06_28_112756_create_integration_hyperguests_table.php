<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationHyperguestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_hyperguests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('subscription_id');
            $table->string('token');
            $table->string('username');
            $table->string('email');
            $table->string('endpoint');
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
        Schema::dropIfExists('integration_hyperguests');
    }
}
