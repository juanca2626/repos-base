<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('file_code', 25);
            $table->tinyInteger('status')->comment('Los posibles valores deben ser 0 = inactivo y 1 = activo. este parametro es para indicar si el file esta activo o inactivo.');

            $table->enum('reservator_type', ['excecutive', 'client']);
            $table->bigInteger('client_id');
            $table->bigInteger('executive_id');

            $table->string('customer_name', 250);
            $table->string('customer_country', 3);

            $table->float('total_hotels_taxes', 10, 2)->default(0.00);
            $table->float('total_hotels_services', 10, 2)->default(0.00);
            $table->float('total_hotels_discounts', 10, 2)->default(0.00);
            $table->float('total_hotels_subs', 10, 2)->default(0.00);
            $table->float('total_hotels', 10, 2)->default(0.00);

            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('update_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

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
        Schema::dropIfExists('reservations');
    }
}
