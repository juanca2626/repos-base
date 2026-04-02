<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSerieDeparturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serie_departures', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('serie_header_id'); // NN
            $table->string('name');
            $table->boolean('has_holiday')->default(false);
            $table->string('name_holiday')->nullable();
            $table->boolean('has_extra_departure')->default(false);
            $table->string('link_guidelines')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('serie_header_id')
                ->references('id')
                ->on('serie_headers')
                ->onDelete('cascade');

            $table->index('serie_header_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serie_departures', function (Blueprint $table) {
            $table->dropForeign(['serie_header_id']);
        });

        Schema::dropIfExists('serie_departures');
    }
}
