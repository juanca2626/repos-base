<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDeletedAtToClientExecutivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_executives', function (Blueprint $table) {
            $table->softDeletes();
        });
        $today = \Carbon\Carbon::now();
        \Illuminate\Support\Facades\DB::statement('update client_executives set deleted_at = "'.$today.'";');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_executives', function (Blueprint $table) {
            $table->removeColumn(['deleted_at']);
        });
    }
}
