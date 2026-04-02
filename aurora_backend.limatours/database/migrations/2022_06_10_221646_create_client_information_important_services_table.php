<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientInformationImportantServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_information_important_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('info_important_service_id');
            $table->foreign('info_important_service_id','in_im_srv_foreign')
                ->references('id')
                ->on('information_important_services')
                ->onDelete('cascade');
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id','cli_in_im_srv_foreign')
                ->references('id')->on('clients')->onDelete('cascade');
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
        Schema::dropIfExists('client_information_important_services');
    }
}
