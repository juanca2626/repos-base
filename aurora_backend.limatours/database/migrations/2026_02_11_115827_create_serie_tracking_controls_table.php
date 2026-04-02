<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSerieTrackingControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serie_tracking_controls', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('serie_departure_program_id'); // NN

            $table->integer('file')->nullable();
            $table->string('passenger_group_name')->nullable();
            $table->integer('qty_passengers')->nullable();

            $table->unsignedBigInteger('client_id'); // NN
            $table->unsignedBigInteger('user_id');   // NN

            $table->string('ticket_mapi')->nullable();
            $table->string('observation')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('serie_departure_program_id')
                ->references('id')
                ->on('serie_departure_programs')
                ->onDelete('cascade');

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('restrict');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');

            $table->index('serie_departure_program_id');
            $table->index('client_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serie_tracking_controls', function (Blueprint $table) {
            $table->dropForeign(['serie_departure_program_id']);
            $table->dropForeign(['client_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('serie_tracking_controls');
    }
}
