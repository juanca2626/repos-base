<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsFilesQuoteServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {    
        Schema::table('quote_services', function (Blueprint $table) { 
            $table->boolean('is_file')->default(0);      
            $table->bigInteger('file_itinerary_id')->nullable();     
            $table->boolean('file_status')->default(0);       
            $table->decimal('file_amount_sale', 10, 2)->default(0);
            $table->decimal('file_amount_cost', 10, 2)->default(0);
             
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quote_services', function (Blueprint $table) {

            $table->dropColumn(['is_file']);
            $table->dropColumn(['file_itinerary_id']);
            $table->dropColumn(['file_status']);
            $table->dropColumn(['file_amount_sale']);
            $table->dropColumn(['file_amount_cost']);
 
        });
    }
}
