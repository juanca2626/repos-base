<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSupplementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suplements', function (Blueprint $table) {
            $table->boolean('per_person')->default(false);
            $table->boolean('per_room')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suplements', function (Blueprint $table) {
            $table->dropColumn(['per_person','per_room']);
        });
    }
}
