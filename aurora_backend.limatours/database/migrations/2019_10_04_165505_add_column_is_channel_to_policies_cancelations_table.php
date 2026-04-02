<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsChannelToPoliciesCancelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasColumn('policies_cancelations', 'is_channel')){
            Schema::table('policies_cancelations', function (Blueprint $table) {
                $table->tinyInteger('is_channel')->default(0);
            });
        }

        if (!Schema::hasColumn('policies_cancelations', 'code')){
            Schema::table('policies_cancelations', function (Blueprint $table) {
                $table->tinyInteger('code')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('policies_cancelations', 'is_channel')){
            Schema::table('policies_cancelations', function (Blueprint $table) {
                $table->dropColumn('is_channel');
            });
        }

        if (Schema::hasColumn('policies_cancelations', 'code')){
            Schema::table('policies_cancelations', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }
}
