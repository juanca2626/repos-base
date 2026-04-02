<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSerieDepartureProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serie_departure_programs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('serie_program_id');   // NN
            $table->unsignedBigInteger('serie_departure_id'); // NN
            $table->date('date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('serie_program_id')
                ->references('id')
                ->on('serie_programs')
                ->onDelete('cascade');

            $table->foreign('serie_departure_id')
                ->references('id')
                ->on('serie_departures')
                ->onDelete('cascade');

            $table->index(
                ['serie_program_id', 'serie_departure_id'],
                'sdp_program_departure_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serie_departure_programs', function (Blueprint $table) {
            $table->dropForeign(['serie_program_id']);
            $table->dropForeign(['serie_departure_id']);
        });

        Schema::dropIfExists('serie_departure_programs');
    }
}
