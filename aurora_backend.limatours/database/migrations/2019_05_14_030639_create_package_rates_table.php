<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('reference_number');
            $table->decimal('simple', 10, 2)->nullable();
            $table->decimal('double', 10, 2)->nullable();
            $table->decimal('triple', 10, 2)->nullable();
            $table->decimal('boy', 10, 2)->nullable();
            $table->decimal('infant', 10, 2)->nullable();
            $table->dateTime('date_from');
            $table->dateTime('date_to');

            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('type_class_id');
            $table->unsignedBigInteger('service_type_id');

            $table->foreign("package_id")->references('id')->on('packages');
            $table->foreign("type_class_id")->references('id')->on('type_classes');

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
        Schema::dropIfExists('package_rates');
    }
}
