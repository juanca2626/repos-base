<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteColumnsServiceSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->dropColumn('day');
            $table->dropColumn('from');
            $table->dropColumn('to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_schedules', function (Blueprint $table) {
            $table->time('from');
            $table->time('to');
            $table->enum('day',
                [
                    'L', //Lunes
                    'M', //Martes
                    'X', //Miercoles
                    'J', //Jueves
                    'V', //Viernes
                    'S', //Sabado
                    'D', //Domingo
                ]);
        });
    }
}
