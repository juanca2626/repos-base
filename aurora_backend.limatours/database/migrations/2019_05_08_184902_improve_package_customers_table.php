<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImprovePackageCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers_packages', function (Blueprint $table) {
            $table->dropForeign(['pack_id']);
        });

        Schema::table('customers_packages', function (Blueprint $table) {
            $table->renameColumn('pack_id', 'package_id');
            $table->foreign('package_id')->references('id')->on('packages');
        });

        Schema::rename('customers_packages', 'package_customers');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('package_customers', 'customers_packages');

        Schema::table('customers_packages', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->renameColumn('package_id', 'pack_id');
        });

        Schema::table('customers_packages', function (Blueprint $table) {
            $table->foreign('pack_id')->references('id')->on('packages');;
        });
    }
}
