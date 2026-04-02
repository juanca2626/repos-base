<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientPackageOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_package_offers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('package_plan_rate_id');
            $table->foreign("client_id")->references('id')->on('clients');
            $table->foreign("package_plan_rate_id")->references('id')->on('package_plan_rates');
            $table->date('date_from');
            $table->date('date_to');
            $table->double('value',8,2);
            $table->boolean('is_offer')->default(1);
            $table->softDeletes();
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
        Schema::dropIfExists('client_package_offers');
    }
}
