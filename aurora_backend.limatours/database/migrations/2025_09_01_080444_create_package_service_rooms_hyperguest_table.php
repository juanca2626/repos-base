<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageServiceRoomsHyperguestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_service_rooms_hyperguest', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Llaves foráneas
            $table->unsignedBigInteger('package_service_id')->nullable();
            $table->unsignedBigInteger('rate_plan_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();

            // Campos numéricos
            $table->tinyInteger('num_adult')->default(0);
            $table->tinyInteger('num_infant')->default(0);

            // Campos de precio
            $table->decimal('price_adult', 10, 4)->default(0);
            $table->decimal('price_child', 10, 4)->default(0);
            $table->decimal('price_infant', 10, 4)->default(0);
            $table->decimal('price_extra', 10, 4)->default(0);
            $table->decimal('price_amount_base', 10, 4)->default(0);
            $table->decimal('price_amount_total', 10, 4)->default(0);

             // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Constraints de llaves foráneas
            $table->foreign('package_service_id')->references('id')->on('package_services')->onDelete('cascade');
            $table->foreign('rate_plan_id')->references('id')->on('rates_plans')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_service_rooms_hyperguest');
    }
}
