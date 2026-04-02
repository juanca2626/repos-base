<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientPackageSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_package_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('package_id');
            $table->unsignedSmallInteger('reservation_from')->comment('Campo: reserva a partir de');
            $table->unsignedSmallInteger('unit_duration_reserve')->default(2)->comment('1 - horas; 2 - dias');
            $table->foreign("client_id")->references('id')->on('clients');
            $table->foreign("package_id")->references('id')->on('packages');
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
        Schema::dropIfExists('client_package_settings');
    }
}
