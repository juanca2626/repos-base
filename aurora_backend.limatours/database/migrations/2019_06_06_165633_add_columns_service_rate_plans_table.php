<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsServiceRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_rate_plans', function (Blueprint $table) {
            $table->boolean('status')->after('date_to');
            $table->decimal('price_guide', 8, 2)->after('date_to');
            $table->decimal('price_infant', 8, 2)->after('date_to');
            $table->decimal('price_child', 8, 2)->after('date_to');
            $table->decimal('price_adult', 8, 2)->after('date_to');
            $table->integer('pax_to')->after('date_to');
            $table->integer('pax_from')->after('date_to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_rate_plans', function (Blueprint $table) {
            $table->dropColumn(['status', 'price_guide', 'price_infant', 'price_child', 'price_adult', 'pax_to', 'pax_from']);
        });
    }
}
