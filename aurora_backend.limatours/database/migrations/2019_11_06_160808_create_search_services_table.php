<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('index_search');
            $table->json('origin');
            $table->json('destiny');
            $table->date('date');
            $table->tinyInteger('quantity_adults');
            $table->tinyInteger('quantity_child');
            $table->json('quantity_persons');
            $table->json('type_services')->nullable();
            $table->json('service_sub_categories')->nullable();
            $table->json('experiences')->nullable();
            $table->json('classifications')->nullable();
            $table->string('filter_by_name')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('client_id');
            $table->uuid('token_search');
            $table->integer('quantity_services');
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
        Schema::dropIfExists('search_services');
    }
}
