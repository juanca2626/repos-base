<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCancelationOrReconfirmationTablePoliciesCancellationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policies_cancelations', function (Blueprint $table) {
            $table->enum('type',['cancellations','reconfirmations'])->default('cancellations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('policies_cancelations', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
