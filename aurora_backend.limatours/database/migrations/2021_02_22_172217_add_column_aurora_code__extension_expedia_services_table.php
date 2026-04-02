<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAuroraCodeExtensionExpediaServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('extension_expedia_services', function(Blueprint $table){
            $table->string('aurora_code')->after('ticket_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('extension_expedia_services', function(Blueprint $table){
            $table->removeColumn(['aurora_code']);
        });
    }
}
