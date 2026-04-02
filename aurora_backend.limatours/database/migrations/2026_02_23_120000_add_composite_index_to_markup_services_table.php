<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompositeIndexToMarkupServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('markup_services', function (Blueprint $table) {
            $table->index(['period', 'service_id', 'client_id'], 'markup_services_period_service_client_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('markup_services', function (Blueprint $table) {
            $table->dropIndex('markup_services_period_service_client_idx');
        });
    }
}
