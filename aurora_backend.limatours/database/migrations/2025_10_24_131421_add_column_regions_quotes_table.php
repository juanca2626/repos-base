<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnRegionsQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotes', function (Blueprint $table) {                        
            $table->unsignedBigInteger('quote_id_multi_region_to')->nullable()->comment('ID de la cotizacion creada que tendra la union de varias cotizaciones');
            $table->unsignedBigInteger('quote_id_multi_region_from')->nullable()->comment('ID de la cotizacion padre');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
