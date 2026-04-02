<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDataToQuoteServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE quote_services CHANGE COLUMN type type ENUM(
                    'hotel', 'service', 'flight'
        ) NOT NULL");

        Schema::table('quote_services', function (Blueprint $table) {
            $table->string('code_flight')->nullable();
            $table->string('origin')->nullable();
            $table->string('destiny')->nullable();
            $table->date('date_flight')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE quote_services CHANGE COLUMN type type ENUM(
                    'hotel', 'service'
        ) NOT NULL");

        Schema::table('quote_services', function (Blueprint $table) {
            $table->dropColumn('code_flight');
            $table->dropColumn('origin');
            $table->dropColumn('destiny');
            $table->dropColumn('date_flight');
        });
    }
}
