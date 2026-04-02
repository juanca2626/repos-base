<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('aurora_code', 10);
            $table->string('name', 350);
            $table->unsignedBigInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->double('latitude');
            $table->double('longitude');
            $table->integer('qty_reserve');
            $table->char('equivalence_aurora', 10);
            $table->boolean('affected_igv');
            $table->boolean('apply_igv');
            $table->decimal('igv', 8, 2);
            $table->boolean('allow_guide');
            $table->boolean('allow_child');
            $table->boolean('allow_infant');
            $table->smallInteger('limit_confirm_hours');
            $table->smallInteger('infant_min_age');
            $table->smallInteger('infant_max_age');
            $table->boolean('include_accommodation');

            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('units');

            $table->unsignedBigInteger('unit_duration_id');
            $table->foreign('unit_duration_id')->references('id')->on('unit_durations');

            $table->unsignedBigInteger('service_type_id');
            $table->foreign('service_type_id')->references('id')->on('service_types');

            $table->unsignedBigInteger('classification_id');
            $table->foreign('classification_id')->references('id')->on('classifications');

            $table->unsignedBigInteger('service_sub_category_id');
            $table->foreign('service_sub_category_id')->references('id')->on('service_sub_categories');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->dateTime('date_solicitude');
            $table->char('stela_code', 10);
            $table->integer('pax_min');
            $table->integer('pax_max');
            $table->integer('min_age');
            $table->char('equivalence_stela', 10);
            $table->boolean('require_itinerary')->default(false);
            $table->boolean('require_image_itinerary')->default(false);
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('services');
    }
}
