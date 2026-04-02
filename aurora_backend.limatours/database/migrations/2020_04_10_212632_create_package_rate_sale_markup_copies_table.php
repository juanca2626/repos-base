<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageRateSaleMarkupCopiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_rate_sale_markup_copies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('object_id');
            $table->bigInteger('package_id');
            $table->morphs('seller');
            $table->float('markup')->default(0);
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('package_plan_rate_id');
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
        Schema::dropIfExists('package_rate_sale_markup_copies');
    }
}
