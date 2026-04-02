<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTourcmsComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tourcms_components', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tourcms_header_id');
            $table->foreign('tourcms_header_id')->references('id')->on('tourcms_headers');
            $table->integer('component_id');
            $table->integer('linked_component_id');
            $table->integer('product_id');
            $table->integer('date_id');
            $table->string('date_type', 45)->nullable();
            $table->string('product_code', 45)->nullable();
            $table->string('date_code', 45)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('local_payment')->nullable();
            $table->integer('customer_payment')->nullable();
            $table->dateTime('component_added_datetime')->nullable();
            $table->char('start_time', 5)->nullable();
            $table->char('end_time', 5)->nullable();
            $table->text('component_name')->nullable();
            $table->text('product_note')->nullable();
            $table->text('component_note')->nullable();
            $table->string('rate_breakdown', 10)->nullable();
            $table->text('rate_description')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->text('redeem')->nullable();
            $table->tinyInteger('product_type')->nullable();
            $table->integer('sale_quantity')->nullable();
            $table->string('sale_quantity_rule', 45)->nullable();
            $table->double('sale_tax_percentage')->nullable();
            $table->tinyInteger('sale_tax_inclusive')->nullable();
            $table->char('sale_currency', 3)->nullable();
            $table->double('sale_price')->nullable();
            $table->double('tax_total')->nullable();
            $table->double('sale_price_inc_tax_total')->nullable();
            $table->double('sale_exchange_rate')->nullable();
            $table->char('currency_base', 3)->nullable();
            $table->double('sale_price_base')->nullable();
            $table->double('tax_total_base')->nullable();
            $table->double('sale_price_inc_tax_total_base')->nullable();
            $table->integer('cost_quantity')->nullable();
            $table->string('cost_quantity_rule', 45)->nullable();
            $table->double('cost_tax_percentage')->nullable();
            $table->integer('cost_tax_inclusive')->nullable();
            $table->char('cost_currency', 3)->nullable();
            $table->double('cost_price')->nullable();
            $table->double('cost_tax_total')->nullable();
            $table->double('cost_price_inc_tax_total')->nullable();
            $table->double('cost_exchange_rate')->nullable();
            $table->double('cost_price_base')->nullable();
            $table->double('cost_tax_total_base')->nullable();
            $table->double('cost_price_inc_tax_total_base')->nullable();
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
        Schema::dropIfExists('tourcms_components');
    }
}
