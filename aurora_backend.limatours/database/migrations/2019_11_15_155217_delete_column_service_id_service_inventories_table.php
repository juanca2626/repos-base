<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteColumnServiceIdServiceInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('service_inventories', function (Blueprint $table) {
            $table->dropForeign('service_inventories_service_id_foreign');
            $table->dropColumn('service_id');
        });
        Schema::EnableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_inventories', function (Blueprint $table) {

        });
    }
}
