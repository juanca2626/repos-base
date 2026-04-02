<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCloneToMarkups extends Migration
{
    public function up()
    {
        Schema::table('markups', function (Blueprint $table) {
            $table->integer('clone')->default(0)->after('client_id'); // Agrega la columna después de client_id
        });
    }

    public function down()
    {
        Schema::table('markups', function (Blueprint $table) {
            $table->dropColumn('clone'); // Elimina la columna si se revierte la migración
        });
    }
}
