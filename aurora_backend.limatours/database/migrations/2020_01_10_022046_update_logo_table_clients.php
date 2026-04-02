<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateLogoTableClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('clients')->update([
            'logo' => DB::raw(
                'CONCAT("https://res.cloudinary.com/litomarketing/image/upload/aurora/logos/",logo)'
            )
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('clients')->update([
            'logo' => DB::raw(
                'CONCAT("https://res.cloudinary.com/litomarketing/image/upload/aurora/logos/","masi.png")'
            )
        ]);
    }
}
