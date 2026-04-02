<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLogDataChannelLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channels_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('reservation_id')->after('id')->default(0);
            $table->text('log_request')->nullable()->after('token')->comment('Los que nos responde el endpoint');
            $table->text('log_response')->nullable()->after('token')->comment('Lo que enviamos al endpoint');
            $table->char('type_data', 4)->nullable()->after('token')->comment('Posibles valores: xml,json');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('channels_logs', function (Blueprint $table) {
            //
        });
    }
}
