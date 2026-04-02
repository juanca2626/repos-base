<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLinkedinClientEcommerceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_ecommerce', function (Blueprint $table) {
            $table->text('linkedin')->nullable()->after('instagram');
            $table->string('email_questions')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_ecommerce', function (Blueprint $table) {
            $table->dropColumn('linkedin');
            $table->dropColumn('email_questions');
        });
    }
}
