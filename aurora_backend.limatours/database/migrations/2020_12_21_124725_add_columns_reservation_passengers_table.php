<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsReservationPassengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservation_passengers', function (Blueprint $table) {
            $table->char('doctype_iso',5)->nullable()->after('document_type_id');
            $table->string('email')->nullable()->after('genre');
            $table->string('phone')->nullable()->after('email');
            $table->char('country_iso',5)->nullable()->after('phone');
            $table->longText('dietary_restrictions')->nullable()->after('country_iso');
            $table->longText('medical_restrictions')->nullable()->after('dietary_restrictions');
            $table->longText('notes')->nullable()->after('medical_restrictions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservation_passengers', function (Blueprint $table) {
            //
        });
    }
}
