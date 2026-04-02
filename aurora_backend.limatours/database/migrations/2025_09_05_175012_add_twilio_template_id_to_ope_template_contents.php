<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwilioTemplateIdToOpeTemplateContents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ope_template_contents', function (Blueprint $table) {
            $table->string('twilio_template_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ope_template_contents', function (Blueprint $table) {
            $table->dropColumn('twilio_template_id');
        });
    }
}
