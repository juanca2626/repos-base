<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsTourcmsComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tourcms_components', function (Blueprint $table) {
            $table->string('net_quantity_rule')->after('sale_price_inc_tax_total_base')->nullable();
            $table->integer('net_price_quantity')->after('sale_price_inc_tax_total_base')->nullable();
            $table->double('net_price_tax_total')->after('sale_price_inc_tax_total_base')->nullable();
            $table->double('net_price_inc_tax_total')->after('sale_price_inc_tax_total_base')->nullable();
            $table->double('net_price')->after('sale_price_inc_tax_total_base')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tourcms_components', function (Blueprint $table) {
            $table->dropColumn(['net_quantity_rule','net_price_quantity','net_price_tax_total','net_price_inc_tax_total','net_price']);
        });
    }
}
