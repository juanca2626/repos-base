<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailContactColumnOnIntegrationHyperguestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_hyperguests', function (Blueprint $table) {
            $table->longText('email_contact')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_hyperguests', function (Blueprint $table) {
            $table->dropColumn(['email_contact']);
        });
    }
}
