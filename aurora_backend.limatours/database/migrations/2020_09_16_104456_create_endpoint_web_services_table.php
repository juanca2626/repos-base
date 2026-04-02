<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEndpointWebServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('endpoint_web_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description');
            $table->text('endpoint');
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        $seeder = new EndPointWebServiceSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('endpoint_web_services');
    }
}
