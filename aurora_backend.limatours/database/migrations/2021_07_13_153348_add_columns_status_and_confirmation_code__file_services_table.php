<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsStatusAndConfirmationCodeFileServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_services', function (Blueprint $table) {
            $table->string('status_hotel', 2)->nullable()->after('status_ifx');
            $table->string('confirmation_code', 15)->nullable()->after('status_ifx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_services', function (Blueprint $table) {
            $table->dropColumn(['status_hotel', 'confirmation_code']);
        });
    }
}
