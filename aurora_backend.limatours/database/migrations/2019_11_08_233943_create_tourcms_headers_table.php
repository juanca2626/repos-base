<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTourcmsHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tourcms_headers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tourcms_channel_id');
            $table->foreign('tourcms_channel_id')->references('id')->on('tourcms_channels');
            $table->integer('booking_id');
            $table->integer('channel_id');
            $table->integer('account_id');
            $table->text('channel_name')->nullable();
            $table->dateTime('made_date_time')->nullable();
            $table->text('made_username')->nullable();
            $table->char('made_type', 1)->nullable();
            $table->text('made_name')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('booking_name')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->text('status_text')->nullable();
            $table->text('voucher_url')->nullable();
            $table->text('barcode_data')->nullable();
            $table->integer('cancel_reason')->nullable();
            $table->text('cancel_text')->nullable();
            $table->dateTime('cancel_date_time')->nullable();
            $table->tinyInteger('final_check')->nullable();
            $table->integer('lead_customer_id')->nullable();
            $table->text('lead_customer_name')->nullable();
            $table->text('lead_customer_email')->nullable();
            $table->tinyInteger('lead_customer_travelling')->nullable();
            $table->tinyInteger('customer_count')->nullable();
            $table->char('sale_currency', 3)->nullable();
            $table->double('sales_revenue')->nullable();
            $table->string('sales_revenue_display', 20)->nullable();
            $table->double('deposit')->nullable();
            $table->string('deposit_display', 20)->nullable();
            $table->text('agent_type')->nullable();
            $table->integer('agent_id')->nullable();
            $table->integer('marketplace_agent_id')->nullable();
            $table->text('agent_name')->nullable();
            $table->text('agent_code')->nullable();
            $table->text('agent_ref')->nullable();
            $table->integer('tracking_miscid')->nullable();
            $table->double('commission')->nullable();
            $table->double('commission_tax')->nullable();
            $table->char('commission_currency', 3)->nullable();
            $table->string('commission_display', 20)->nullable();
            $table->string('commission_tax_display', 20)->nullable();
            $table->double('booking_has_net_price')->nullable();
            $table->tinyInteger('payment_status')->nullable();
            $table->text('payment_status_text')->nullable();
            $table->char('balance_owed_by', 1)->nullable();
            $table->double('balance')->nullable();
            $table->string('balance_display', 20)->nullable();
            $table->date('balance_due')->nullable();
            $table->string('customers_agecat_breakdown', 45)->nullable();
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
        Schema::dropIfExists('tourcms_headers');
    }
}
