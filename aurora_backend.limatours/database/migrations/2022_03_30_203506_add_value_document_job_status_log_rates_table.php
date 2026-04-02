<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddValueDocumentJobStatusLogRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("SET SQL_SAFE_UPDATES = 0;");
        \Illuminate\Support\Facades\DB::statement("UPDATE `aurora_prod`.`log_rates` SET `document_job_status` = '1'");
        \Illuminate\Support\Facades\DB::statement("SET SQL_SAFE_UPDATES = 1;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
