<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnUserIdToClientIdPackageCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        App\PackageCustomer::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Schema::table('package_customers', function (Blueprint $table) {

            $table->renameColumn('user_id','client_id');
            $table->dropColumn('markup');
            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_customers', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->renameColumn('client_id','user_id');
            $table->double('markup')->nullable()->after('status');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
}
