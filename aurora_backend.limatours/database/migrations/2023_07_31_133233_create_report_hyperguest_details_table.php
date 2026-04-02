<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportHyperguestDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_hyperguest_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('report_hyperguest_id')->unsigned();
            $table->string('booking_id')->unique();             
            $table->integer('property_id');
            $table->string('property_name');
            $table->string('property_country');
            $table->string('property_city');
            $table->string('lead_guest_name');
            $table->integer('adults');
            $table->integer('children');
            $table->integer('infants');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->decimal('price_amount',10,2);
            $table->string('price_currency');
            $table->string('status');
            $table->integer('partner_id');
            $table->string('partner_name');
            $table->string('agency_reference');
            $table->dateTime('booking_date');
            $table->dateTime('cancellation_deadline');
            $table->string('file_code');  
            $table->string('status_aurora');  
            $table->decimal('price_aurora',10,2);                                          
            $table->string('reservations_hotels_rates_plans_room_ids');  
            $table->timestamps();
            $table->foreign('report_hyperguest_id')->references('id')->on('report_hyperguests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_hyperguest_details');
    }
}
