<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('aurora_code', 6);
            $table->tinyInteger('status');

            $table->unsignedBigInteger('train_rail_route_id');
            $table->unsignedBigInteger('train_train_class_id');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('state_id');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('district_id');
            $table->unsignedBigInteger('zone_id')->nullable();

            $table->boolean('allow_child')->nullable()->default(false);
            $table->boolean('allow_infant')->nullable()->default(false);
            $table->integer('infant_min_age')->nullable();
            $table->integer('infant_max_age')->nullable();

            $table->foreign('train_rail_route_id')->references('id')->on('train_rail_routes');
            $table->foreign('train_train_class_id')->references('id')->on('train_train_classes');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('state_id')->references('id')->on('states');
            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('district_id')->references('id')->on('districts');
            $table->foreign('zone_id')->references('id')->on('zones');

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
        Schema::dropIfExists('train_templates');
    }
}
