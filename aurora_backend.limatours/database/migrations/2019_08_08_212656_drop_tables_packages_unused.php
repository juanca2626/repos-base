<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTablesPackagesUnused extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('package_permissions');
        Schema::dropIfExists('package_destination_days');
        Schema::dropIfExists('package_destinations');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_permissions');
        Schema::dropIfExists('package_destination_days');
        Schema::dropIfExists('package_destinations');
    }
}
