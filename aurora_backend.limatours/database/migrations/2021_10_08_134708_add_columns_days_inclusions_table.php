<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsDaysInclusionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inclusions', function (Blueprint $table) {
            $table->boolean('sunday')->default(1)->after('id');
            $table->boolean('saturday')->default(1)->after('id');
            $table->boolean('friday')->default(1)->after('id');
            $table->boolean('thursday')->default(1)->after('id');
            $table->boolean('wednesday')->default(1)->after('id');
            $table->boolean('tuesday')->default(1)->after('id');
            $table->boolean('monday')->default(1)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inclusions', function (Blueprint $table) {
            //
        });
    }
}
