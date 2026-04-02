<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_packages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('type_class_id')->nullable();
            $table->unsignedBigInteger('service_type_id')->nullable();
            $table->string('name')->nullable();
            $table->integer('quantity_adults')->default(0);
            $table->integer('quantity_child_with_bed')->default(0);
            $table->integer('quantity_child_without_bed')->default(0);
            $table->smallInteger('quantity_sgl')->default(0);
            $table->smallInteger('quantity_dbl')->default(0);
            $table->smallInteger('quantity_tpl')->default(0);
            $table->float('price_per_adult_sgl', 10, 2)->default(0.00);
            $table->float('price_per_adult_dbl', 10, 2)->default(0.00);
            $table->float('price_per_adult_tpl', 10, 2)->default(0.00);
            $table->float('price_per_child_with_bed', 10, 2)->default(0.00);
            $table->float('price_per_child_without_bed', 10, 2)->default(0.00);
            $table->foreign('reservation_id')->references('id')->on('reservations');
            $table->foreign('type_class_id')->references('id')->on('type_classes');
            $table->foreign('service_type_id')->references('id')->on('service_types');
            $table->foreign('package_id')->references('id')->on('packages');
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
        Schema::dropIfExists('reservations_packages');
    }
}
