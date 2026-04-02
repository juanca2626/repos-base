<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveMultiRegionFieldsFromQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn([
                'quote_id_multi_region_to',
                'quote_id_multi_region_from',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->unsignedBigInteger('quote_id_multi_region_to')->nullable();
            $table->unsignedBigInteger('quote_id_multi_region_from')->nullable();
        });
    }
}
